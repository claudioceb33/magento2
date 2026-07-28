<?php
declare(strict_types=1);

namespace Ceb\CartQuantity\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Data extends AbstractHelper
{

    protected const CONFIG_PLP = 'themeceb/settings_plp/';
    protected const QTY_INPUT   = 'qty_box';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Json
     */
    protected $json;

    /**
     * Data constructor.
     *
     * @param Context              $context
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Json $json
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
        parent::__construct($context);
    }

    /**
     * @param $field
     *
     * @return mixed
     */
    protected function getConfigPlp($field)
    {
        return $this->scopeConfig->getValue(self::CONFIG_PLP . $field);
    }
    /**
     * @return bool
     */
    public function isQtyInput()
    {
        return (bool)(int)$this->getConfigPlp(self::QTY_INPUT);
    }
}
