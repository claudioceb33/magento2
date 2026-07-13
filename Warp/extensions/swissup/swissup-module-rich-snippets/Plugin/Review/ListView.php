<?php
namespace Swissup\RichSnippets\Plugin\Review;

class ListView extends \Swissup\RichSnippets\Plugin\AbstractPlugin
{
    /**
     * Plugin after method toHtml
     *
     * @param  \Magento\Review\Block\Product\View\ListView $subject
     * @param  string $html
     * @return string
     */
    public function afterToHtml(
        \Magento\Review\Block\Product\View\ListView $subject,
        $html
    ){
        if ($this->isMicrodataDisabled()) {
            $searchArray = [
                ' itemscope itemprop="review" itemtype="http://schema.org/Review"',
                ' itemprop="name"',
                ' itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating"',
                ('<meta itemprop="worstRating" content = "1"/>' . "\n"),
                ' itemprop="worstRating"',
                ('<meta itemprop="bestRating" content = "100"/>' . "\n"),
                ' itemprop="bestRating"',
                ' itemprop="ratingValue"',
                ' itemprop="description"',
                ' itemprop="author"',
                ' itemprop="datePublished"'
            ];
            $html = str_replace($searchArray, '', $html);
        }

        return $html;
    }
}
