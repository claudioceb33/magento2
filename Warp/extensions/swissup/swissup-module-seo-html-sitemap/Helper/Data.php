<?php
namespace Swissup\SeoHtmlSitemap\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    public function groupCollectionByFirstLetter($items)
    {
        $previousGroup = null;
        $groupedItems = [];

        foreach ($items as $item) {
            if ($item->getName()) {
                $name = $item->getName();
            } else {
                $name = $item->getTitle();
            }

            $currentGroup = $this->getGroup($name);

            if (!$this->isSameGroup($previousGroup, $currentGroup)) {
                $groupedItems[$currentGroup] = [];
                $previousGroup = $currentGroup;
            }

            $groupedItems[$previousGroup][] = $item;
        }

        return $groupedItems;
    }

    private function getGroup($word)
    {
        if (function_exists('mb_strtolower')) {
            $word = mb_strtolower($word);
        }
        if (function_exists('mb_substr')) {
            return mb_substr($word, 0, 1, 'UTF-8');
        }

        return substr($word, 0, 1);
    }

    private function isSameGroup($group1, $group2)
    {
        if (is_numeric($group1) && is_numeric($group2)) {
            return true;
        } else {
            return $group1 == $group2;
        }
    }

    public function getGroupTitle($group)
    {
        if (is_numeric($group)) {
            return '#';
        }

        return $group;
    }
}
