<?php

namespace Swissup\SeoPager\Plugin\Block\Html;

use Magento\Theme\Block\Html\Pager as Subject;
use Swissup\SeoPager\Helper\Data;

class Pager
{
    private Data $helper;

    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    public function afterToHtml(
        Subject $subject,
        string $result
    ) {
        $block = $subject;
        $html = $result;
        $this->addViewAllLink($html, $block->getChildHtml('viewAllLink'));

        return $html;
    }

    private function addViewAllLink(
        string &$pagerHtml,
        string $linkHtml
    ): void {
        if ($linkHtml
            && ($position = strpos($pagerHtml, '</ul>')) !== false
        ) {
            $pagerHtml = substr_replace(
                $pagerHtml,
                $linkHtml,
                $position,
                0
            );
        }

    }
}
