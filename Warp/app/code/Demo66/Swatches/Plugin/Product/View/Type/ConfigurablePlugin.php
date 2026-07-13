<?php
namespace Demo66\Swatches\Plugin\Product\View\Type;

use Magento\Framework\Serialize\Serializer\Json;
use Demo66\Swatches\Helper\Data;

class ConfigurablePlugin {
    /**
     * @var Json
     */
    protected $json;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Configurable constructor.
     *
     * @param Json                       $json
     * @param Data                       $helperData
     */
    public function __construct(
        Json $json,
        Data $helperData
    ) {
        $this->json = $json;
        $this->helperData = $helperData;
    }

    public function afterGetJsonConfig(\Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject, $result)
    {
        $result =   $this->json->unserialize($result);
        $jsonResult = $this->helperData->processJsonConfig($result, $subject->getAllowProducts());
        return $this->json->serialize($jsonResult);
    }
}
