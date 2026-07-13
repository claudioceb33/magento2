<?php

namespace Swissup\SeoUrls\Block\Adminhtml\Attribute;

use Magento\Backend\Block\Widget\Form\Generic;

class Advanced extends Generic
{
    /**
     * @var \Swissup\SeoUrls\Model\Config\Source\NofollowStategy
     */
    private $nofollowStartegySource;

    /**
     * @var \Swissup\SeoUrls\Model\Attribute
     */
    private $seoAttribute;

    /**
     * @param \Magento\Backend\Block\Template\Context              $context
     * @param \Magento\Framework\Registry                          $registry
     * @param \Magento\Framework\Data\FormFactory                  $formFactory
     * @param \Swissup\SeoUrls\Model\Config\Source\NofollowStategy $nofollowStartegySource
     * @param \Swissup\SeoUrls\Model\Attribute                     $seoAttribute
     * @param array                                                $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Swissup\SeoUrls\Model\Config\Source\NofollowStategy $nofollowStartegySource,
        \Swissup\SeoUrls\Model\Attribute $seoAttribute,
        array $data = []
    ) {
        $this->nofollowStartegySource = $nofollowStartegySource;
        $this->seoAttribute = $seoAttribute;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $attributeObject = $this->getAttributeObject();

        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset(
            'seo_urls_advanced_fieldset',
            ['legend' => __('Advanced Properties'), 'collapsable' => true]
        );

        $fieldset->addField(
            'seourl_add_nofollow',
            'select',
            [
                'name' => 'seourl_add_nofollow',
                'label' => __('Links with `nofollow` in Layered Navigation'),
                'title' => __('Links with `nofollow` in Layered Navigation'),
                'values' => $this->nofollowStartegySource->toOptionArray(),
                'value' => $this->seoAttribute->isNofollow($attributeObject)
            ]
        );

        $this->setForm($form);
        return $this;
    }

    /**
     * Retrieve attribute object from registry
     *
     * @return mixed
     */
    private function getAttributeObject()
    {
        return $this->_coreRegistry->registry('entity_attribute');
    }
}
