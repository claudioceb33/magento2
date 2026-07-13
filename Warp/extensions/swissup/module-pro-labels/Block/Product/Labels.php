<?php
namespace Swissup\ProLabels\Block\Product;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Swissup\ProLabels\Model\LabelsProvider;

class Labels extends Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var LabelsProvider
     */
    protected $labelsProvider;

    /**
     * @param \Magento\Framework\Registry $registry
     * @param LabelsProvider              $labelsProvider
     * @param Template\Context            $context
     * @param array                       $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        LabelsProvider $labelsProvider,
        Template\Context $context,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->labelsProvider = $labelsProvider;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getBaseImageWrapConfig()
    {
        return $this->_scopeConfig->getValue(
            'prolabels/general/base',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getContentWrapConfig()
    {
        return $this->_scopeConfig->getValue(
            'prolabels/general/content',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return ProductInterface
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('product');
    }

    /**
     * @param  ProductInterface[]  $products
     * @return void
     */
    public function preloadLabels(array $products)
    {
        $this->labelsProvider->preloadManualForProducts($products, 'product');
    }

    /**
     * Get labels for product on product page.
     *
     * @param  ProductInterface $product
     * @return \Magento\Framework\DataObject
     */
    public function getProductLabels(ProductInterface $product)
    {
        return $this->labelsProvider->initialize($product, 'product');
    }
}
