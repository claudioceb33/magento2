<?php
namespace Swissup\FieldManager\Controller\Adminhtml\Index;

use Magento\Framework\DataObject;

class Validate extends \Swissup\FieldManager\Controller\Adminhtml\Index
{
    const DEFAULT_MESSAGE_KEY = 'message';
    const RESERVED_ATTRIBUTES = [];

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $response = new DataObject();
        $response->setError(false);

        $attributeCode = $this->getRequest()->getParam('attribute_code');
        $attributeId = $this->getRequest()->getParam('attribute_id');
        $frontendLabel = $this->getRequest()->getParam('frontend_label');
        $attributeCode = $attributeCode ?: $this->generateCode($frontendLabel[0]);

        // prevent overriding address data
        if (!$attributeId && in_array($attributeCode, static::RESERVED_ATTRIBUTES)) {
            $message = __(
                'The attribute code \'%1\' is reserved by system. Please try another attribute code',
                $attributeCode
            );
            $this->setMessageToResponse($response, [$message]);
            $response->setError(true);

            return $this->resultJsonFactory->create()->setJsonData($response->toJson());
        }

        $field = $this->initModel()->loadByCode($this->entityTypeId, $attributeCode);
        if ($field->getId() && !$attributeId) {
            $message = strlen($this->getRequest()->getParam('attribute_code'))
                ? __('An field with this code already exists.')
                : __('An field with the same code (%1) already exists.', $attributeCode);

            $this->setMessageToResponse($response, [$message]);
            $response->setError(true);
            $response->setProductAttribute($field->toArray());
        }

        return $this->resultJsonFactory->create()->setJsonData($response->toJson());
    }

    /**
     * Set message to response object
     *
     * @param DataObject $response
     * @param string[] $messages
     * @return DataObject
     */
    private function setMessageToResponse($response, $messages)
    {
        $messageKey = $this->getRequest()->getParam('message_key', static::DEFAULT_MESSAGE_KEY);
        if ($messageKey === static::DEFAULT_MESSAGE_KEY) {
            $messages = reset($messages);
        }

        return $response->setData($messageKey, $messages);
    }
}
