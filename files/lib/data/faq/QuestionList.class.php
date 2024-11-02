<?php

namespace wcf\data\faq;

use wcf\data\DatabaseObjectList;

/**
 * @method  Question        current()
 * @method  Question[]       getObjects()
 * @method  Question|null    getSingleObject()
 * @method  Question|null    search($objectID)
 * @property    Question[] $objects
 */
final class QuestionList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $sqlOrderBy = 'showOrder, questionID';
}
