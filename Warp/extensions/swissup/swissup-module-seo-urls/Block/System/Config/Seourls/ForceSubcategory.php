<?php

namespace Swissup\SeoUrls\Block\System\Config\Seourls;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ForceSubcategory extends Field
{
    protected function _renderValue(AbstractElement $element)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get(\Swissup\SeoUrls\Helper\Data::class);
        if ($helper->isModuleOutputEnabled('Swissup_Ajaxlayerednavigation')) {
            $comment = $element->getComment();
            $comment .= $comment ? '<br>' : '';
            $title = __('Enable multiple apply for categories');
            $comment .= __('And disables setting "%1" for Swissup Ajax Layered Navigation.', $title);
            $element->setComment($comment);
        }

        return parent::_renderValue($element);
    }
}
