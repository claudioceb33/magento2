<?php
declare(strict_types=1);

namespace Ceb\Installments\Plugin\Catalog\Block\Product;

use Ceb\Installments\Block\Product\Listing\Installments;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Model\Product;

class AbstractProductPlugin
{
    /**
     * @var string
     */
    protected const BRAND_END_MARKER = '<!-- ceb_brand_end -->';

    /**
     * @param AbstractProduct $subject
     * @param string $result
     * @param Product $product
     * @return string
     */
    public function afterGetProductDetailsHtml(
        AbstractProduct $subject,
        $result,
        Product $product
    ): string {
        $installmentsBlock = $subject->getLayout()->createBlock(Installments::class);
        if (!$installmentsBlock) {
            return (string)$result;
        }

        $installmentsHtml = $installmentsBlock
            ->setProduct($product)
            ->setTemplate('Ceb_Installments::product/listing/installments.phtml')
            ->toHtml();
        if ($installmentsHtml === '') {
            return (string)$result;
        }

        $result = (string)$result;
        $brandEndPosition = strpos($result, self::BRAND_END_MARKER);
        if ($brandEndPosition === false) {
            return $installmentsHtml . $result;
        }

        $brandEndPosition += strlen(self::BRAND_END_MARKER);

        return substr($result, 0, $brandEndPosition)
            . $installmentsHtml
            . substr($result, $brandEndPosition);
    }
}
