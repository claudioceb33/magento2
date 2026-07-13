<?php
namespace Demo66\Swatches\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use \Mtools\Core\Helper\Image;

class Data extends AbstractHelper
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Image
     */
    protected $helperImage;

    /**
     * @var GetSalableQuantityDataBySku
     */
    protected $qtyData;

    /**
     * Data constructor.
     *
     * @param ScopeConfigInterface           $scopeConfig
     * @param GetSalableQuantityDataBySku    $qtyData
     * @param Image                          $helperImage
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        GetSalableQuantityDataBySku $qtyData,
        Image $helperImage
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->qtyData = $qtyData;
        $this->helperImage = $helperImage;
    }

    /**
     * Add extra info to JsonConfig
     *
     * @param array $jsonResult
     * @param \Magento\Catalog\Model\Product $products
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return array
     */
    public function processJsonConfig($jsonResult, $products)
    {
        foreach ($products as $simpleProduct) {
            $id = $simpleProduct->getId();
            foreach ($simpleProduct->getAttributes() as $attribute) {
                if (is_object($attribute) && (($attribute->getIsVisible() && $attribute->getIsVisibleOnFront())
                        || in_array($attribute->getAttributeCode(), ['sku','description','name','image']))) {
                    // <= Here you can put any attribute you want to see dynamically
                    $code = $attribute->getAttributeCode();
                    $value = (string)$attribute->getFrontend()->getValue($simpleProduct);
                    if ($code == 'image' && !empty($value)) {
                        try {
                            $value = $this->helperImage->resize('catalog/product/' . $value, 448, 550);
                        } catch (\Magento\Framework\Exception\FileSystemException $e) {
                            $e->getMessage();
                        }
                    }
                    $jsonResult['dynamic'][$code][$id] = ['value' => $value];
                    $jsonResult['dynamic']['url'][$id] = ['value' => $simpleProduct->getProductUrl()];
                }
            }
        }

        $jsonResult['dynamic']['stock'] = $this->getProductOptionStock($jsonResult);

        return $jsonResult;
    }

    /**
     * Adds Stock info to options
     *
     * @param array $jsonResult
     *
     * @return array
     */
    protected function getProductOptionStock($jsonResult)
    {
        $pids = [];
        if (isset($jsonResult['dynamic']['sku'])) {
            foreach ($jsonResult['dynamic']['sku'] as $pid => $sku) {
                $stock = $this->qtyData->execute($sku['value']);
                if (isset($stock[0]['qty'])) {
                    $pids[$pid]['qty'] = $stock[0]['qty'];
                    $pids[$pid]['option'] = reset($jsonResult['index'][$pid]);
                    $pids[$pid]['attrId'] = array_key_first($jsonResult['index'][$pid]);
                    $pids[$pid]['attrCode'] = $jsonResult['attributes'][ $pids[$pid]['attrId'] ]['code'];
                    $pids[$pid]['product'] = $pid;
                    $pids[$pid]['element'] = '#option-label-'.$pids[$pid]['attrCode'].'-'
                        .$pids[$pid]['attrId'].'-item-'.$pids[$pid]['option'];
                    $pids[$pid]['option'.$pids[$pid]['option']] = $pid;
                }
            }
        }
        return $pids;
    }
}
