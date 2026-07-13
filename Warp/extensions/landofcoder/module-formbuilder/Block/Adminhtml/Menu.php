<?php

/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_Formbuilder
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\Formbuilder\Block\Adminhtml;

use Lof\All\Model\Config;
use Magento\Backend\Block\Template\Context;

class Menu extends \Magento\Backend\Block\Template
{
    /**
     * @var null|array
     */
    protected ?array $items = null;

    /**
     * Block template filename
     *
     * @var string
     */
    protected $_template = 'Lof_All::menu.phtml';


    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * get menu items
     *
     * @return mixed|array
     */
    public function getMenuItems()
    {
        if ($this->items === null) {
            $items = [
                'form/edit'    => [
                    'title'    => __('Add New Form'),
                    'url'      => $this->getUrl('*/form/index'),
                    'resource' => 'Lof_Formbuilder::form'
                ],
                'form'    => [
                    'title'    => __('Manage Forms'),
                    'url'      => $this->getUrl('*/form/index'),
                    'resource' => 'Lof_Formbuilder::form'
                ],
                'message' => [
                    'title'    => __('Manage Messages'),
                    'url'      => $this->getUrl('*/message/index'),
                    'resource' => 'Lof_Formbuilder::message'
                ],
                'model' => [
                    'title'    => __('Manage Models'),
                    'url'      => $this->getUrl('*/model/index'),
                    'resource' => 'Lof_Formbuilder::model'
                ],
                'modelcategory' => [
                    'title'    => __('Manage Model Categories'),
                    'url'      => $this->getUrl('*/modelcategory/index'),
                    'resource' => 'Lof_Formbuilder::category'
                ],
                'blacklist' => [
                    'title'    => __('Manage Blacklist'),
                    'url'      => $this->getUrl('*/blacklist/index'),
                    'resource' => 'Lof_Formbuilder::blacklist'
                ],
                'reply' => [
                    'title'    => __('Manage Replies'),
                    'url'      => $this->getUrl('*/reply/index'),
                    'resource' => 'Lof_Formbuilder::reply'
                ],
                'settings' => [
                    'title'    => __('Settings'),
                    'url'      => $this->getUrl('adminhtml/system_config/edit/section/lofformbuilder'),
                    'resource' => 'Lof_Formbuilder::config_form',
                ],
                'readme'   => [
                    'title'     => __('Guide'),
                    'url'       => Config::FORMBUILDER_GUIDE,
                    'attr'      => ['target' => '_blank'],
                    'separator' => true,
                ],
                'support'  => [
                    'title' => __('Get Support'),
                    'url'   => Config::LANDOFCODER_TICKET,
                    'attr'  => ['target' => '_blank'],
                ],
            ];
            foreach ($items as $index => $item) {
                if (array_key_exists('resource', $item)) {
                    if (!$this->_authorization->isAllowed($item['resource'])) {
                        unset($items[$index]);
                    }
                }
            }

            $this->items = $items;
        }

        return $this->items;
    }


    /**
     * @return array
     */
    public function getCurrentItem()
    {
        $items          = $this->getMenuItems();
        $controllerName = $this->getRequest()->getControllerName();
        if (array_key_exists($controllerName, $items)) {
            return $items[$controllerName];
        }

        return $items['page'];
    }


    /**
     * @param array $item
     * @return string
     */
    public function renderAttributes(array $item)
    {
        $result = '';
        if (isset($item['attr'])) {
            foreach ($item['attr'] as $attrName => $attrValue) {
                $result .= sprintf(' %s=\'%s\'', $attrName, $attrValue);
            }
        }

        return $result;
    }


    /**
     * @param $itemIndex
     * @return bool
     */
    public function isCurrent($itemIndex)
    {
        return $itemIndex == $this->getRequest()->getControllerName();
    }
}
