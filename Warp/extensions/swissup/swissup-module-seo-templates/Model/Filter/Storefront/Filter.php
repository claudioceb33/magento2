<?php

namespace Swissup\SeoTemplates\Model\Filter\Storefront;

class Filter extends \Swissup\SeoCore\Model\Filter\AbstractFilter
{
    /**
     * Translate directive at storefront
     *
     * @param  array $construction
     * @return string
     */
    public function i18nDirective($construction)
    {
        $getIncludeParameters = [$this, '_getIncludeParameters'];
        if (!$params = $getIncludeParameters($construction[2])) {
            $params = $getIncludeParameters(' text='.trim($construction[2]));
        }

        $text = $params['text'] ?? null;

        return $text ? __($text) : '';
    }
}
