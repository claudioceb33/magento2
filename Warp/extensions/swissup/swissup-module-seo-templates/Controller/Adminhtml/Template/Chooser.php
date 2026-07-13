<?php

namespace Swissup\SeoTemplates\Controller\Adminhtml\Template;

use Magento\Framework\Controller\ResultFactory;

class Chooser extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_SeoTemplates::template_save';

    /**
     * Prepare block for chooser
     * Inspired by \Magento\CatalogRule\Controller\Adminhtml\Promo\Widget\Chooser::execute
     *
     * @return void
     */
    public function execute()
    {
        $request = $this->getRequest();

        switch ($request->getParam('attribute')) {
            case 'applied_filters':
                $block = $this->_view->getLayout()->createBlock(
                    \Swissup\SeoTemplates\Block\Adminhtml\Chooser\Filters::class,
                    'promo_widget_chooser_applied_filters',
                    [
                        'data' => [
                            'js_form_object' => $request->getParam('form')
                        ]
                    ]
                );
                break;

            default:
                $block = false;
                break;
        }

        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }
}
