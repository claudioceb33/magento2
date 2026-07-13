<?php

namespace Swissup\RichSnippets\Block\Widget;

use Swissup\RichSnippets\Block\LdJson;

class Faq extends LdJson
{
    protected $_template = 'widget/faq.phtml';

    private array $faqs;

    public function getLdJson()
    {
        $ldArray = [
            "@context" => "https://schema.org",
            "@type" => "FAQPage",
            "mainEntity" => array_map(function ($faq) {
                return [
                    "@type" => "Question",
                    "name" => $faq->getQuestion(),
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => $faq->getAnswer()
                    ]
                ];
            }, $this->getFaqs())
        ];

        return $this->prepareJsonString($ldArray);
    }

    public function getFaqs(): array
    {
        if (!isset($this->faqs)) {
            $rawquestions = json_decode($this->getData('questions'), true);
            $this->faqs = array_map(function ($item) {
                return new \Magento\Framework\DataObject([
                    'question' => $item['question'],
                    'answer' => $item['answer']
                ]);
            }, $rawquestions);
        }

        return $this->faqs;
    }

    public function isHtmlBlockAllowed(): bool
    {
        $output = $this->getData('output') ?: 'jsonhtml';

        return $output === 'jsonhtml';
    }
}
