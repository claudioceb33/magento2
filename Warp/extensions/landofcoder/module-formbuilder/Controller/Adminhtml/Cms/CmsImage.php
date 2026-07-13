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
 * @package    Lof_FormbuilderOrder
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\Formbuilder\Controller\Adminhtml\Cms;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Data;
use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Cms\Model\Wysiwyg\Config;

class CmsImage extends Action
{
    public const ADMIN_RESOURCE = 'Lof_Formbuilder::form_edit';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;
    /**
     * @var Images
     */
    protected $imagesHelper;

    /**
     * Adminhtml data
     *
     * @var Data|null
     */
    protected $backendData = null;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Images $imagesHelper
     * @param Data $backendData
     * @param array $data
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Images $imagesHelper,
        Data $backendData,
        array $data = []
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->imagesHelper         = $imagesHelper;
        $this->backendData         = $backendData;
        parent::__construct($context, $resultJsonFactory, $data);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileBrowserUrlParams = [
            'target_element_id' => 'editor' . time() . rand(),
            'as_is' => 'ves'
        ];

        $fileBrowserUrlParams['current_tree_path'] = $this->imagesHelper->idEncode(Config::IMAGE_DIRECTORY);
        $imageUrl = $this->backendData->getUrl('cms/wysiwyg_images/index', $fileBrowserUrlParams);
        $data = [
            'url' => $imageUrl,
            'target_element_id' => $fileBrowserUrlParams['target_element_id']
        ];

        return $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($data)
        );
    }
}
