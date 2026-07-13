<?php
declare(strict_types=1);

namespace Ecommerce66\Widgets\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class Popup extends Template implements BlockInterface
{
    protected $_template = 'Ecommerce66_Widgets::widget/popup.phtml';

    public function getBlockContent()
    {
        $blockId = $this->getData('block_id');
        if ($blockId) {
            return $this->getLayout()->createBlock(\Magento\Cms\Block\Block::class)->setBlockId($blockId)->toHtml();
        }
        return '';
    }
}