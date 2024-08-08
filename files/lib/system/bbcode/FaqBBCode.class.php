<?php

namespace wcf\system\bbcode;

use DOMDocument;
use wcf\data\faq\Question;
use wcf\system\WCF;

final class FaqBBCode extends AbstractBBCode
{
    /**
     * @inheritDoc
     */
    public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser): string
    {
        $questionID = null;
        if (isset($openingTag['attributes'][0])) {
            $questionID = (int)$openingTag['attributes'][0];
        }

        if ($questionID === null) {
            return '';
        }

        $question = new Question($questionID);
        if (!$question->questionID || !$question->isAccessible()) {
            return '';
        }

        if ($parser->getOutputType() === 'text/html') {
            $collapse = false;

            $doc = new DOMDocument();
            @$doc->loadHTML($question->getFormattedOutput());
            if ($doc->getElementsByTagName('p')->length > 5 || $doc->getElementsByTagName('br')->length > 5) {
                $collapse = true;
            }

            return WCF::getTPL()->fetch('faqBBCode', 'wcf', [
                'question' => $question,
                'collapseQuestion' => $collapse,
            ], true);
        }

        return $question->getTitle() . "\n\n" . $question->getPlainOutput();
    }
}
