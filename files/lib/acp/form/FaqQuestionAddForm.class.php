<?php

namespace wcf\acp\form;

use Laminas\Diactoros\Response\RedirectResponse;
use Override;
use wcf\data\faq\category\FaqCategoryNodeTree;
use wcf\data\faq\Question;
use wcf\data\faq\QuestionAction;
use wcf\data\faq\QuestionEditor;
use wcf\data\IStorableObject;
use wcf\data\language\item\LanguageItemList;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\container\TabFormContainer;
use wcf\system\form\builder\container\TabMenuFormContainer;
use wcf\system\form\builder\container\wysiwyg\WysiwygFormContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\language\LanguageFactory;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

class FaqQuestionAddForm extends AbstractFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.faq.questions.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.faq.canAddQuestion'];

    /**
     * @inheritDoc
     */
    public $objectActionClass = QuestionAction::class;

    /**
     * @inheritDoc
     */
    public $objectEditLinkController = FaqQuestionEditForm::class;

    /**
     * list of available languages
     * @var Language[]
     */
    protected array $availableLanguages = [];

    protected int $isMultilingual = 0;

    protected array $multiLingualAnswers = [];

    protected array $categories;

    #[Override]
    public function readParameters()
    {
        parent::readParameters();

        // get available languages
        $this->availableLanguages = LanguageFactory::getInstance()->getLanguages();

        if (!empty($_REQUEST['isMultilingual'])) {
            $this->isMultilingual = 1;
        }

        if (isset($_GET['duplicateID'])) {
            $question = new Question($_GET['duplicateID']);
            if ($question->questionID) {
                $this->formObject = $question;
                if ($this->formObject->isMultilingual) {
                    $this->isMultilingual = 1;

                    $languageItemList = new LanguageItemList();
                    $languageItemList->getConditionBuilder()->add('languageItem = ?', [$this->formObject->answer]);
                    $languageItemList->readObjects();
                    foreach ($languageItemList as $languageItem) {
                        $this->multiLingualAnswers[$languageItem->languageID] = $languageItem->languageItemValue;
                    }
                }
            }
        }

        // work-around to force adding faq via dialog overlay
        if (\count($this->availableLanguages) > 1 && empty($_POST) && !isset($_REQUEST['isMultilingual'])) {
            return new RedirectResponse(LinkHandler::getInstance()->getLink('FaqQuestionList', [
                'showFaqAddDialog' => 1,
            ]));
        }
    }

    #[Override]
    protected function createForm()
    {
        parent::createForm();

        $tabContent = [];
        if ($this->isMultilingual) {
            foreach ($this->availableLanguages as $language) {
                $tabContent[] = TabFormContainer::create('tab_' . $language->languageID)
                    ->label($language->languageName)
                    ->appendChildren([
                        WysiwygFormContainer::create('answer_i18n_' . $language->languageID)
                            ->label('wcf.acp.faq.question.answer')
                            ->messageObjectType('dev.tkirch.wsc.faq.question')
                            ->attachmentData('dev.tkirch.wsc.faq.question')
                            ->required(),
                    ]);
            }
        }

        $this->form->appendChildren([
            FormContainer::create('general')
                ->label('wcf.acp.faq.question.general')
                ->appendChildren([
                    SingleSelectionFormField::create('categoryID')
                        ->label('wcf.acp.faq.category')
                        ->options($this->getCategories())
                        ->required(),
                    TextFormField::create('question')
                        ->label('wcf.acp.faq.question.question')
                        ->i18n()
                        ->languageItemPattern('wcf.faq.question.question\d+')
                        ->required(),
                ]),
            (
                $this->isMultilingual
                ? TabMenuFormContainer::create('tabsContainer')
                    ->appendChildren($tabContent)
                : WysiwygFormContainer::create('answer')
                    ->label('wcf.acp.faq.question.answer')
                    ->messageObjectType('dev.tkirch.wsc.faq.question')
                    // ->messageLanguageItemPattern('wcf.faq.question.answer\d+')
                    ->attachmentData('dev.tkirch.wsc.faq.question')
                    ->required()
            ),
            FormContainer::create('position')
                ->label('wcf.category.position')
                ->appendChildren([
                    IntegerFormField::create('showOrder')
                        ->label('wcf.global.showOrder')
                        ->step(1)
                        ->minimum(1)
                        ->value(QuestionEditor::getShowOrder()),
                ]),
            FormContainer::create('settings')
                ->label('wcf.acp.faq.question.settings')
                ->appendChildren([
                    BooleanFormField::create('isDisabled')
                        ->label('wcf.acp.faq.question.isDisabled'),
                ]),
        ]);
    }

    #[Override]
    public function finalizeForm()
    {
        parent::finalizeForm();

        $this->form->getDataHandler()->addProcessor(new CustomFormDataProcessor(
            'answer_i18n',
            static function (IFormDocument $document, array $parameters) {
                foreach ($parameters['data'] as $key => $val) {
                    if (\str_starts_with($key, 'answer_i18n_')) {
                        unset($parameters['data'][$key]);
                    }
                }
                foreach ($parameters as $key => $val) {
                    if (
                        \str_starts_with($key, 'answer_i18n')
                        && \str_ends_with($key, 'htmlInputProcessor')
                        && $val instanceof HtmlInputProcessor
                    ) {
                        $parts = \explode('_', $key);
                        $parameters['answer_i18n'][(int)$parts[2]] = $val->getHtml();
                    }
                }

                return $parameters;
            },
            function (IFormDocument $document, array $data, IStorableObject $object) {
                foreach ($this->multiLingualAnswers as $languageID => $answer) {
                    $data['answer_i18n_' . $languageID] = $answer;
                }

                return $data;
            }
        ));
    }

    #[Override]
    protected function setFormAction()
    {
        $parameters = [
            'isMultilingual' => $this->isMultilingual,
        ];
        if ($this->formObject !== null) {
            if ($this->formObject instanceof IRouteController) {
                $parameters['object'] = $this->formObject;
            } else {
                $object = $this->formObject;

                $parameters['id'] = $object->{$object::getDatabaseTableIndexName()};
            }
        }

        $this->form->action(LinkHandler::getInstance()->getControllerLink(static::class, $parameters));
    }

    #[Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'categories' => $this->getCategories(),
        ]);
    }

    protected function getCategories(): array
    {
        if (!isset($this->categories)) {
            $categoryTree = new FaqCategoryNodeTree('dev.tkirch.wsc.faq.category');
            $categoryTree->setMaxDepth(0);
            $categoryList = $categoryTree->getIterator();

            $this->categories = [];
            foreach ($categoryList as $category) {
                $this->categories[$category->categoryID] = $category;

                $childCategories = $category->getAllChildCategories();
                if (!\count($childCategories)) {
                    continue;
                }

                foreach ($childCategories as $childCategory) {
                    $childCategory->setPrefix();
                    $this->categories[$childCategory->categoryID] = $childCategory;
                }
            }
        }

        return $this->categories;
    }
}
