<?php

namespace Swissup\SeoTemplates\Model\Filter;

class Filter extends \Swissup\SeoCore\Model\Filter\AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    protected function _postprocessResult($result, $paramsArray)
    {
        $i18nKeys = ['prefix', 'sufix'];
        foreach ($i18nKeys as $key) {
            $param = &$paramsArray[$key] ?? null;
            if (!$param) {
                continue;
            }

            if (strpos($param, 'i18n:') === 0) {
                $param = substr($param, strlen('i18n:'));
                $param = '{{i18n "' . $param . '"}}';
            }
        }

        return parent::_postprocessResult($result, $paramsArray);
    }

    /**
     * Translate directive (pass it to storefront filter processing)
     *
     * @param  array $construction
     * @return string
     */
    public function i18nDirective($construction)
    {
        return '{{i18n' . $construction[2] . '}}';
    }

    /**
     * Randomize directive.
     * Randomly outputs string from pipe-separated list.
     *
     * @param  array $construction
     * @return string
     */
    public function randomDirective($construction)
    {
        $getIncludeParameters = [$this, '_getIncludeParameters'];
        if (!$params = $getIncludeParameters($construction[2])) {
            if (trim($construction[2])) {
                $params = $getIncludeParameters(' text='.trim($construction[2]));
            }
        }

        $text = reset($params) ?? '';
        $array = explode('|', $text);

        return $array[rand(0, count($array) - 1)];
    }
}
