<?php

namespace wcf\system\cache\builder;

use Override;
use wcf\data\faq\QuestionList;

final class FaqQuestionListCacheBuilder extends AbstractCacheBuilder
{
    #[Override]
    protected function rebuild(array $parameters)
    {
        $questionList = new QuestionList();
        $questionList->readObjects();

        $questions = [];
        foreach ($questionList as $question) {
            $questions[$question->categoryID][] = $question;
        }

        return [$questions, $questionList->getObjectIDs()];
    }
}
