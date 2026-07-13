<?php

namespace Ecommerce66\ProductTabs\Helper;

/**
 * Contact base helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $_attributeCollection;

    /**
     * Data constructor.
     *
     * @param \Magento\Store\Model\StoreManagerInterface                               $storeManager
     * @param \Magento\Framework\ObjectManagerInterface                                $objectManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollection
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollection
    ) {
        $this->_storeManager = $storeManager;
        $this->_objectManager = $objectManager;
        $this->_attributeCollection = $attributeCollection;
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * @param $model
     *
     * @return mixed
     */
    public function getModel($model)
    {
        return $this->_objectManager->create($model);
    }

    /**
     * @param $node
     *
     * @return mixed
     */
    public function getStoreConfig($node)
    {
        return $this->_scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    public function getAttributeCollection()
    {
        return $this->_attributeCollection->create()->addVisibleFilter();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTabsCollection()
    {
        $storeId = $this->getStore()->getId();
        $websiteId = $this->getStore()->getWebsiteId();
        $collection = $this->getModel('Ecommerce66\ProductTabs\Model\Protabs')
            ->getCollection()
            ->addFieldToFilter('scope', 'stores')
            ->addFieldToFilter('scope_id', $storeId)
            ->setOrder('position', 'ASC');

        if ($collection->getSize()==0) {
            $collection  = $this->getModel('Ecommerce66\ProductTabs\Model\Protabs')
                ->getCollection()
                ->addFieldToFilter('scope', 'websites')
                ->addFieldToFilter('scope_id', $websiteId)
                ->setOrder('position', 'ASC');

            if ($collection->getSize()==0) {
                $collection  = $this->getModel('Ecommerce66\ProductTabs\Model\Protabs')
                    ->getCollection()
                    ->addFieldToFilter('scope', 'default')
                    ->setOrder('position', 'ASC');
            }
        }
        return $collection;
    }

    /**
     * @param $attributeCode
     *
     * @return string
     */
    public function convertAttributeToCallName($attributeCode)
    {
        $arrText = explode("_", $attributeCode);
        $result = 'get';
        if (count($arrText) <= 1) {
            $result .= ucfirst($arrText[0]);
        }
        if (count($arrText) > 1) {
            foreach ($arrText as $_text) {
                $result .= ucfirst($_text);
            }
        }
        return $result;
    }

    /**
     * @param string $attributeCode
     *
     * @return string
     */
    public function getAttributeType($attributeCode)
    {
        $attribute = $this->getAttributeCollection()
            ->addFieldToFilter('attribute_code', $attributeCode)
            ->getFirstItem();
        if ($attribute->getFrontendInput() == 'select') {
            return 'text';
        }
        if ($attribute->getFrontendInput() == 'multiselect') {
            return 'list';
        }
        return 'none';
    }
}
