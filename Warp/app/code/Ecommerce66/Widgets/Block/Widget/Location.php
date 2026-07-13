<?php
declare(strict_types=1);

namespace Ecommerce66\Widgets\Block\Widget;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class Location extends Base
{
    /**
     * @var string
     */
    protected $_template = 'Ecommerce66_Widgets::widget/location.phtml';

    /**
     * @param $dataKey
     *
     * @return string
     */
    public function getDataImage($dataKey)
    {
         return $this->getMediaUrl() . $this->getData($dataKey);
    }

}
