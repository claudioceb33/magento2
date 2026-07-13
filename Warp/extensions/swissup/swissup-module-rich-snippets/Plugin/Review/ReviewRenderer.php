<?php
namespace Swissup\RichSnippets\Plugin\Review;

class ReviewRenderer extends \Swissup\RichSnippets\Plugin\AbstractPlugin
{
    /**
     * Plugin after method toHtml
     *
     * @param  \Magento\Review\Block\Product\ReviewRenderer $subject
     * @param  string $html
     * @return string
     */
    public function afterToHtml(
        \Magento\Review\Block\Product\ReviewRenderer $subject,
        $html
    ){
        if ($this->isMicrodataDisabled()) {
            $searchArray = [
                'itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating"',
                ' itemprop="aggregateRating" itemscope',
                ' itemtype="http://schema.org/AggregateRating"',
                ' itemprop="ratingValue"',
                ' itemprop="bestRating"',
                ' itemprop="reviewCount"'
            ];
            $html = str_replace($searchArray, '', $html);
        }

        return $html;
    }
}
