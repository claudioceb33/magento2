<?php
declare(strict_types=1);

namespace Ecommerce66\Widgets\Block\Widget;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class Countdown extends Base
{

    /**
     * @var string
     */
    protected $_template = "Ecommerce66_Widgets::widget/countdown.phtml";

    /**
     * @param $dataKey
     *
     * @return mixed|string
     */
    public function getDataImage($dataKey)
    {
        $image = $this->getData($dataKey);
        return $this->decodeImageUrl($image);
    }

}
