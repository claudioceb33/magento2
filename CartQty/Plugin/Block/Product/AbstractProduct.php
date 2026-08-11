<?php

namespace Ceb\CartQty\Plugin\Block\Product;

class AbstractProduct
{

    /**
     * @var \Ceb\CartQty\Helper\Data
     */
    protected $dataHelper;

    public function __construct(

        \Ceb\CartQty\Helper\Data $dataHelper

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
                ->createBlock(\Ceb\CartQty\Block\Product\CustomQty::class)
                ->setTemplate('Ceb_CartQty::html/qty_box.phtml');
            $renderer->setProduct($product);
            return $result . $renderer->toHtml();
        }

        return $result;
    }
}
