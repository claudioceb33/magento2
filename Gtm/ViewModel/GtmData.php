<?php
namespace Ceb\Gtm\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Ceb\Gtm\Helper\Data;

class GtmData implements ArgumentInterface
{
    protected Data $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    public function isEnabled(): bool
    {
        return $this->helper->isEnabled();
    }

    public function getGtmId(): ?string
    {
        return $this->helper->getGtmId();
    }

    public function getMeasurementId(): ?string
    {
        return $this->helper->getMeasurementId();
    }
}
