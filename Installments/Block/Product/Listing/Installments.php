<?php
declare(strict_types=1);

namespace Ceb\Installments\Block\Product\Listing;

use Ceb\Installments\Helper\Data as InstallmentsHelper;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Installments extends Template
{
    /**
     * @var InstallmentsHelper
     */
    protected $installmentsHelper;

    /**
     * @param Context $context
     * @param InstallmentsHelper $installmentsHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        InstallmentsHelper $installmentsHelper,
        array $data = []
    ) {
        $this->installmentsHelper = $installmentsHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return Product|null
     */
    public function getProduct()
    {
        $product = $this->getData('product');
        if ($product instanceof Product) {
            return $product;
        }

        return null;
    }

    /**
     * @return array
     */
    public function getInstallmentData(): array
    {
        $product = $this->getProduct();
        if ($product === null) {
            return [];
        }

        $installmentId = (int)$product->getData('product_installments');
        if ($installmentId <= 0) {
            return [];
        }

        $installment = $this->installmentsHelper->getInstallmentById($installmentId);
        if (!is_object($installment)) {
            return [];
        }

        return [
            'installments' => (int)$installment->getInstallments(),
            'rate' => (float)$installment->getRate(),
            'disclaimer' => (string)$installment->getDisclaimer()
        ];
    }

    /**
     * @return bool
     */
    public function canRender(): bool
    {
        $installmentData = $this->getInstallmentData();
        if (empty($installmentData)) {
            return false;
        }

        return (int)$installmentData['installments'] > 1 && (float)$installmentData['rate'] >= 1;
    }
}
