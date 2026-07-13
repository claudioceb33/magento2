<?php

namespace Swissup\SeoCrossLinks\Model;

use Swissup\SeoCrossLinks\Model\ResourceModel\Link\CollectionFactory;

class Filter
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var int
     */
    private $mode;

    /**
     * @var int
     */
    private $storeId;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Adds links to the string according to seo cross links rules
     *
     * @param  string $string, replacement_Count $maxReplaceCount
     * @return string
     */
    public function filter($string)
    {
        libxml_use_internal_errors(true);
        $html = $this->prepareHtml($string);
        $dom = new \DOMDocument;
        $dom->loadHTML($html);
        $dom->removeChild($dom->doctype);

        $xpath = new \DOMXPath($dom);
        $textNodes = $xpath->evaluate("//text()");

        if (!$textNodes) {
            return $string;
        }

        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('search_in', $this->getMode())
            ->addFieldToFilter('store_id', $this->getStoreId())
            ->setOrder('link_id');

        $rules = [];
        $pattern = "/[&\/]/";
        foreach ($collection as $link) {
            if (isset($rules[$link->getKeyword()]) ||
                $link->getKeyword() === $link->getUrlPath()) {
                continue;
            }

            $rules[preg_replace($pattern, ' ', $link->getKeyword())] = array(
                'href'      => $link->getUrl(),
                'title'     => $link->getTitle(),
                'count'     => $link->getReplacementCount() ?: 100,
                'class'     => $link->getClass(),
                'target'    => $link->getTarget_attr(),
                'tooltip'   => $link->getTooltip()
            );
        }
        $pregFind = '/\b(' . implode('|', array_keys($rules)) . ')\b/';

        $counters = [];
        $tooltipClass = 'swissup-crosslink-tooltip';
        foreach ($textNodes as $textNode) {
            if (!$this->canFilter($textNode)) {
                continue;
            }

            $parentNode = $textNode->parentNode;
            $sections = preg_split($pregFind, $textNode->nodeValue, -1, PREG_SPLIT_DELIM_CAPTURE);
            foreach($sections as $section) {
                if (!isset($rules[$section])
                    || (isset($counters[$section]) && $counters[$section] >= $rules[$section]['count'])) {

                    $parentNode->insertBefore($dom->createTextNode($section), $textNode);
                    continue;
                }

                if (!isset($counters[$section])) {
                    $counters[$section] = 0;
                }
                $counters[$section]++;

                $link = $dom->createElement('a', $section);
                $link->setAttribute('title', $rules[$section]['title']);
                $link->setAttribute('href', $rules[$section]['href']);
                $link->setAttribute('class', $rules[$section]['class']);
                $link->setAttribute('target', $rules[$section]['target']);
                $link->setAttribute('data-tooltip', $rules[$section]['title']);
                $parentNode->insertBefore($link, $textNode);

                if($rules[$section]['tooltip']) {
                    $link->setAttribute('title', '');
                    $tooltip = $dom->createElement('span', $rules[$section]['title']);
                    $tooltip->setAttribute('class', $tooltipClass);
                    $link->appendChild($tooltip);
                }
            }

            $parentNode->removeChild($textNode);
        }

        $output = str_replace(
            ['<html>', '</html>', '<body>', '</body>'],
            '',
            $dom->saveHTML()
        );

        return $output;
    }

    /**
     * @param  \DomNode $node
     * @return boolean
     */
    private function canFilter($node)
    {
        $parentNode = $node->parentNode;

        while ($parentNode && property_exists($parentNode, 'tagName')) {
            if ($parentNode->tagName === 'a' || $parentNode->tagName === 'script') {
                return false;
            }

            $parentNode = $parentNode->parentNode;
        }

        return true;
    }

    /**
     * @param  string $html
     * @return string
     */
    private function prepareHtml($string)
    {
        $html = mb_encode_numericentity(
            $string,
            [0x80, 0x10FFFF, 0, 0x1FFFFF],
            'UTF-8'
        );

        $regex = '/<script\b[^>]*>(.*?)<\/script>/is';
        $matches = [];
        preg_match_all($regex, $html, $matches);
        foreach ($matches[1] as $script) {
            if (strstr($script, '</')) {
                $html = str_replace($script, str_replace('</', '<\/', $script), $html);
            }
        }

        return $html;
    }

    /**
     * Get stores
     *
     * return string
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * Get stores
     *
     * return string
     */
    public function getStoreId()
    {
        return array_unique([
            0,
            $this->storeId
        ]);
    }

    /**
     * @param int
     */
    public function setMode($value)
    {
        $this->mode = $value;
        return $this;
    }

    /**
     * @return int
     */
    public function getMode()
    {
        return array_unique([
            $this->mode,
            \Swissup\SeoCrossLinks\Model\Link::SEARCH_IN_ALL
        ]);
    }
}
