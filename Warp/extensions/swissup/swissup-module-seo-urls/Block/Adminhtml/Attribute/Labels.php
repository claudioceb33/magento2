<?php

namespace Swissup\SeoUrls\Block\Adminhtml\Attribute;

use Swissup\SeoUrls\Model\ResourceModel\Attribute\View as AttributeView;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;

class Labels extends \Magento\Backend\Block\Template
{
    /**
     * @var AttributeView
     */
    protected $attributeView;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $_template = 'Swissup_SeoUrls::product/attribute/labels.phtml';

    /**
     * @param Registry      $registry
     * @param AttributeView $attributeView
     * @param Context       $context
     * @param array         $data
     */
    public function __construct(
        Registry $registry,
        AttributeView $attributeView,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->attributeView = $attributeView;
    }

    /**
     * Retrieve stores collection with default store
     *
     * @return \Magento\Store\Model\ResourceModel\Store\Collection
     */
    public function getStores()
    {
        if (!$this->hasStores()) {
            $stores = $this->_storeManager->getStores(true);
            ksort($stores);
            $this->setData('stores', $stores);
        }
        return $this->_getData('stores');
    }

    /**
     * Retrieve in-URL labels of attribute for each store
     *
     * @return array
     */
    public function getLabelValues()
    {
        $values = [];
        $storeLabels = $this->attributeView->getInUrlLabels(
            $this->getAttributeObject()
        );
        foreach ($this->getStores() as $store) {
            $values[$store->getId()] = isset($storeLabels[$store->getId()])
                ? $storeLabels[$store->getId()]['value']
                : '';
        }

        return $values;
    }

    /**
     * Retrieve attribute object from registry
     *
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    private function getAttributeObject()
    {
        return $this->registry->registry('entity_attribute');
    }
}
