<?php

namespace wcf\page;

use CuyZ\Valinor\Mapper\MappingError;
use Override;
use wcf\data\attachment\GroupedAttachmentList;
use wcf\data\faq\category\FaqCategory;
use wcf\data\faq\category\FaqCategoryNodeTree;
use wcf\http\Helper;
use wcf\system\cache\builder\FaqQuestionListCacheBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\WCF;

class FaqQuestionListPage extends AbstractPage
{
    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.faq.canViewFAQ'];

    protected array $faqs = [];

    protected int $showFaqAddDialog = 0;

    protected ?FaqCategory $category;

    #[Override]
    public function readParameters()
    {
        parent::readParameters();

        if (!empty($_REQUEST['showFaqAddDialog'])) {
            $this->showFaqAddDialog = 1;
        }

        try {
            $queryParameters = Helper::mapQueryParameters(
                $_GET,
                <<<'EOT'
                    array {
                        id: positive-int|null
                    }
                    EOT
            );

            $this->category = FaqCategory::getCategory($queryParameters['id']);
        } catch (MappingError) {
            throw new IllegalLinkException();
        }
    }

    #[Override]
    public function readData()
    {
        parent::readData();

        //get categories
        $embedObjectIDs = [];
        $categoryTree = new FaqCategoryNodeTree('dev.tkirch.wsc.faq.category');
        [$questionList, $questionIDs] = FaqQuestionListCacheBuilder::getInstance()->getData();
        $attachmentList = $this->getAttachmentList($questionIDs);
        foreach ($categoryTree->getIterator() as $category) {
            if (!$category->isAccessible()) {
                continue;
            }
            if (
                isset($this->category)
                && $this->category !== null
                && $this->category->categoryID != $category->categoryID
            ) {
                continue;
            }

            $faq = [
                'id' => $category->categoryID,
                'title' => WCF::getLanguage()->get($category->title),
                'attachments' => $attachmentList,
                'icon24' => $category->getIcon(24),
                'icon64' => $category->getIcon(64),
                'questions' => [],
            ];

            $questions = $questionList[$category->categoryID] ?? [];
            foreach ($questions as $question) {
                if ($question->isAccessible()) {
                    $faq['questions'][] = $question;
                    if ($question->hasEmbeddedObjects) {
                        $embedObjectIDs[] = $question->questionID;
                    }
                }
            }

            if ($category->getParentNode() && $category->getParentNode()->categoryID) {
                $this->faqs[$category->getParentNode()->categoryID]['sub'][$category->categoryID] = $faq;
            } else {
                $this->faqs[$category->categoryID] = $faq;
            }
        }

        if ($embedObjectIDs !== []) {
            MessageEmbeddedObjectManager::getInstance()->loadObjects(
                'dev.tkirch.wsc.faq.question',
                $embedObjectIDs
            );
        }
    }

    #[Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'faqs' => $this->faqs,
            'showFaqAddDialog' => $this->showFaqAddDialog,
        ]);
    }

    protected function getAttachmentList(array $questionIDs): array|GroupedAttachmentList
    {
        if ($questionIDs === []) {
            return [];
        }

        $attachmentList = new GroupedAttachmentList('dev.tkirch.wsc.faq.question');
        $attachmentList->getConditionBuilder()->add('attachment.objectID IN (?)', [$questionIDs]);
        $attachmentList->readObjects();

        return $attachmentList;
    }
}
