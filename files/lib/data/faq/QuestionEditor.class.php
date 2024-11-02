<?php

namespace wcf\data\faq;

use Override;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\FaqQuestionListCacheBuilder;
use wcf\system\WCF;

/**
 * @method static Question     create(array $parameters = [])
 * @method      Question     getDecoratedObject()
 * @mixin       Question
 */
final class QuestionEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Question::class;

    #[Override]
    public static function resetCache()
    {
        FaqQuestionListCacheBuilder::getInstance()->reset();
    }

    /**
     * Returns the new show order for a object
     */
    public function updateShowOrder(int $showOrder): int
    {
        if ($showOrder === null) {
            $showOrder = \PHP_INT_MAX;
        }

        //check showOrder
        if ($showOrder < $this->showOrder) {
            $sql = "UPDATE	" . static::getDatabaseTableName() . "
				SET	showOrder = showOrder + 1
				WHERE	showOrder >= ?
				AND	 showOrder < ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([
                $showOrder,
                $this->showOrder,
            ]);
        } elseif ($showOrder > $this->showOrder) {
            //get max show order
            $maxShowOrder = self::getShowOrder() - 1;

            //get show order
            if ($showOrder > $maxShowOrder) {
                $showOrder = $maxShowOrder;
            }

            //update databse
            $sql = "UPDATE	" . static::getDatabaseTableName() . "
				SET	showOrder = showOrder - 1
				WHERE	showOrder <= ?
				AND	 showOrder > ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([
                $showOrder,
                $this->showOrder,
            ]);
        }

        //return show order
        return $showOrder;
    }

    /**
     * Returns the show order for a new object
     */
    public static function getShowOrder(): int
    {
        $sql = "SELECT MAX(showOrder) AS showOrder
			FROM " . static::getDatabaseTableName();
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute();
        $row = $statement->fetchArray();

        return !empty($row) ? ($row['showOrder'] + 1) : 1;
    }
}
