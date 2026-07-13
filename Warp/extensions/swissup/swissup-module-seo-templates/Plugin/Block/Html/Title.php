<?php

namespace Swissup\SeoTemplates\Plugin\Block\Html;

use Swissup\SeoCore\Model\RegistryLocator;
use Swissup\SeoTemplates\Model\Template;

class Title
{
    /**
     * @var \Swissup\SeoTemplates\Helper\Data
     */
    private $helper;

    /**
     * @var RegistryLocator
     */
    private $locator;

    /**
     * @param \Swissup\SeoTemplates\Helper\Data $helper
     * @param RegistryLocator                   $registryLocator
     */
    public function __construct(
        \Swissup\SeoTemplates\Helper\Data $helper,
        RegistryLocator $registryLocator
    ) {
        $this->helper = $helper;
        $this->locator = $registryLocator;
    }

    /**
     * @param  \Magento\Theme\Block\Html\Title $subject
     * @param  string                          $result
     * @return string
     */
    public function afterGetPageHeading(
        \Magento\Theme\Block\Html\Title $subject,
        $result
    ) {
        $block = $subject;
        $entityType = $block->getData('seo_templates_entity_type');
        $entity = false;
        switch ($entityType) {
            case Template::ENTITY_TYPE_PRODUCT:
                $entity = $this->locator->getProduct() ?: $entity;
                break;
            case Template::ENTITY_TYPE_CATEGORY:
                $entity = $this->locator->getCategory() ?: $entity;
                break;
        }

        return $entity && $entity->getData('h1_tag') ?
            $entity->getData('h1_tag') :
            $result;
    }
}
