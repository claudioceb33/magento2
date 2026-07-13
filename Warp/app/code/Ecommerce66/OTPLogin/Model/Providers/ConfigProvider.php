<?php

namespace Ecommerce66\OTPLogin\Model\Providers;

use Magento\Checkout\Model\ConfigProviderInterface;
use Ecommerce66\OTPLogin\Helper\Data;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Data
     */
    protected $data;

    /**
     * ConfigProvider constructor.
     *
     * @param Data $data
     */
    public function __construct(
        Data $data
    )
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
       return ['use_otp_auth' => $this->data->isActive() ];
    }

}
