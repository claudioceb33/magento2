<?php

namespace Ceb\CartQuantity\Plugin\Block\Product;

class AbstractProduct
{

    /**
     * @var \Ceb\CartQuantity\Helper\Data
     */
    protected $dataHelper;

    public function __construct(

        \Ceb\CartQuantity\Helper\Data $dataHelper

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
                ->createBlock(\Ceb\CartQuantity\Block\Product\CustomQty::class)
                ->setTemplate('Ceb_CartQuantity::html/qty_box.phtml');
            $renderer->setProduct($product);
            return $result . $renderer->toHtml();
        }

        return $result;
    }
}
