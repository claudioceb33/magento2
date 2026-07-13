<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ProductInquiryTab implements ObserverInterface
{
    /**
     * @var string PARENT_BLOCK_NAME
     */
    const PARENT_BLOCK_NAME = 'product.info.details';

    /**
     * @var string RENDERING_TEMPLATE
     */
    const RENDERING_TEMPLATE = 'Hexamarvel_FlexibleForm::tab_renderer.phtml';

    /**
     * @var \Hexamarvel\FlexibleForm\Helper\Data
     */
    private $tabHelper;

    /**
     * @param \Hexamarvel\FlexibleForm\Helper\Data $tabHelper
     */
    public function __construct(
        \Hexamarvel\FlexibleForm\Helper\Data $tabHelper
    ) {
        $this->tabHelper = $tabHelper;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getLayout();
        $blocks = $layout->getAllBlocks();
        if (!$this->tabHelper->isEnabled() ||
            empty($this->tabHelper->getConfig('hexaform/product_inquiry_settings/tab_title')) ||
            !$this->tabHelper->getConfig('hexaform/product_inquiry_settings/enable_enquiry')
        ) {
            return;
        }

        foreach ($blocks as $key => $block) {
            /** @var \Magento\Framework\View\Element\Template $block */
            if ($block->getNameInLayout() == self::PARENT_BLOCK_NAME) {
                $block->addChild(
                    'forminquiry',
                    \Magento\Catalog\Block\Product\View::class,
                    [
                        'template' => self::RENDERING_TEMPLATE,
                        'title'     =>  $this->tabHelper->getConfig('hexaform/product_inquiry_settings/tab_title'),
                        'jsLayout'      =>  [
                            [
                                'title'         => $this->tabHelper->getConfig('hexaform/product_inquiry_settings/tab_title'),
                                'type'          => 'template',
                                'data'          => [
                                    "type"      => \Hexamarvel\FlexibleForm\Block\Form::class,
                                    "name"      => "product_tabform",
                                    "template"  => "Hexamarvel_FlexibleForm::form.phtml"
                                ],
                                'description'   => '',
                                'sortOrder'     => '100'
                            ]
                        ]
                    ]
                );
            }
        }
    }
}
