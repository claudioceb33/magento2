<?php

namespace Swissup\SeoUrls\Observer;

class BlockHtmlBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(\Magento\Framework\Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getBlock();
        if ($block->getId() == 'product_attribute_tabs'
            && $this->canUseInLayeredNavigation()
        ) {
            $block->addTabAfter(
                'swissup_seo_urls',
                [
                    'label' => __('SEO URLs'),
                    'title' => __('SEO URLs'),
                    'content' => $block->getChildHtml('swissup_seo_urls')
                ],
                'front'
            );
        }
    }

    /**
     * @return boolean
     */
    private function canUseInLayeredNavigation()
    {
        if ($attribute = $this->registry->registry('entity_attribute')) {
            $isFiltetable = $attribute->getData('is_filterable');
            $isFiltetableInSearch = $attribute->getData('is_filterable_in_search');
            if ($isFiltetable || $isFiltetableInSearch) {
                return true;
            }

            return in_array(
                $attribute->getFrontendInput(),
                [
                    'multiselect',
                    'select',
                    'price',
                    'swatch_visual',
                    'swatch_text'
                ]
            );
        }

        return false;
    }
}
