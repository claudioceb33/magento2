<?php

namespace Ecommerce66\CartQuantity\Plugin\Block\Product;

class AbstractProduct
{

    /**
     * @var \Ecommerce66\CartQuantity\Helper\Data
     */
    protected $dataHelper;

    public function __construct(

        \Ecommerce66\CartQuantity\Helper\Data $dataHelper

    ) {

        $this->dataHelper = $dataHelper;

    }
    public function afterGetProductDetailsHtml(
           \Magento\Framework\View\Element\BlockInterface $subject,
            $result,
            \Magento\Catalog\Model\Product $product
    ) {

         if($this->dataHelper->isQtyInput()){
            $renderer = $subject->getLayout()
                ->createBlock(\Ecommerce66\CartQuantity\Block\Product\CustomQty::class)
                ->setTemplate('Ecommerce66_CartQuantity::html/qty_box.phtml');
            $renderer->setProduct($product);
            return $result . $renderer->toHtml();
        }

        return $result;
    }
}
