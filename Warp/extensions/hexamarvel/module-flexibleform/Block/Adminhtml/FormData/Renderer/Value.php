<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Block\Adminhtml\FormData\Renderer;

use Magento\Framework\DataObject;

class Value extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Hexamarvel\FlexibleForm\Model\FormDataFactory
     */
    protected $formDataFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Hexamarvel\FlexibleForm\Model\FormDataFactory $formDataFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Hexamarvel\FlexibleForm\Model\FormDataFactory $formDataFactory,
        \Hexamarvel\FlexibleForm\Model\FieldFactory $fieldFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->formDataFactory = $formDataFactory;
        $this->fieldFactory    = $fieldFactory;
        $this->_storeManager   = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * @param  DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $formData = $this->formDataFactory->create()->load($row->getID());
        $data = json_decode($formData->getValue(), true);
        $index = $this->getColumn()->getIndex();

        $field = $this->fieldFactory->create()->load($index);
        if ($field->getFieldType() == 'image' && isset($data[$index])) {
            $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            return '<a href="' . $mediaUrl . $data[$index] . '" target="_blank"><img src="' . $mediaUrl . $data[$index] . '" alt="" height="50px" width="50px" /></a>';
        }

        if ($field->getFieldType() == 'file' && isset($data[$index])) {
            $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            return '<a href="' . $mediaUrl . $data[$index] . '" target="_blank">View File</a>';
        }

        if ($field->getFieldType() == 'all_files' && isset($data[$index])) {
            $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            return '<a href="' . $mediaUrl . $data[$index] . '" target="_blank">View File</a>';
        }

        if ($field->getFieldType() == 'video' && isset($data[$index])) {
            $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            return '<a href="' . $mediaUrl . $data[$index] . '" target="_blank">View Video</a>';
        }

        return isset($data[$index]) ? htmlspecialchars($data[$index]) : '';
    }
}
