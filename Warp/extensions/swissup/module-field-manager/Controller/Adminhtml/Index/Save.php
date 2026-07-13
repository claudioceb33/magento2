<?php
namespace Swissup\FieldManager\Controller\Adminhtml\Index;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Controller\ResultFactory;

class Save extends \Swissup\FieldManager\Controller\Adminhtml\Index
{
    const ENTITY_TYPE = '';
    const USED_IN_FORMS = '';

    protected $quoteFactory;
    protected $orderFactory;

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $this->preprocessOptionsData($data);
            $fieldId = $this->getRequest()->getParam('attribute_id');
            if (!$fieldId) {
                $attributeCode = $this->getRequest()->getParam('attribute_code')
                    ?: $this->generateCode($this->getRequest()->getParam('frontend_label')[0]);
                if (strlen($attributeCode) > 0) {
                    $validatorAttrCode = $this->regexValidatorFactory->create(['pattern' => '/^[a-z][a-z_0-9]{0,30}$/']);
                    if (!$validatorAttrCode->isValid($attributeCode)) {
                        $this->messageManager->addError(
                            __(
                                'Attribute code "%1" is invalid. Please use only letters (a-z), ' .
                                'numbers (0-9) or underscore(_) in this field, first character should be a letter.',
                                $attributeCode
                            )
                        );

                        return $this->returnResult(
                            '*/*/edit',
                            ['attribute_id' => $fieldId, '_current' => true]
                        );
                    }
                }
                $data['attribute_code'] = $attributeCode;
            }

            $model = $this->initModel();
            if ($fieldId) {
                $model->load($fieldId);
                if (!$model->getId()) {
                    $this->messageManager->addError(__('This field no longer exists.'));

                    return $this->returnResult('*/*/');
                }

                if ($model->getEntityTypeId() != $this->entityTypeId) {
                    $this->messageManager->addError(__('You cannot edit this attribute.'));
                    $this->_session->setFieldData($data);

                    return $this->returnResult('*/*/');
                }

                $data['attribute_code'] = $model->getAttributeCode();
                $data['frontend_input'] = $model->getFrontendInput();
                $data['is_user_defined'] = $model->getIsUserDefined();
                $data['is_system'] = $model->getIsSystem();
            } else {
                $data['is_user_defined'] = 1;
                $data['is_system'] = 0;

                $data['backend_model'] = $this->helper->getBackendModelByInput($data['frontend_input']);
                $data['source_model'] = $this->helper->getSourceModelByInput($data['frontend_input']);
                $data['backend_type'] = $model->getBackendTypeByInput($data['frontend_input']);

                $data['attribute_set_id'] = $this->eavModelConfig
                    ->getEntityType(static::ENTITY_TYPE)
                    ->getDefaultAttributeSetId();
                $data['attribute_group_id'] = $this->eavAttributeSetFactory->create()
                    ->getDefaultGroupId($data['attribute_set_id']);
            }

            if (isset($data['used_in_forms']) && is_array($data['used_in_forms'])) {
                $data['used_in_forms'][] = static::USED_IN_FORMS;
            } else if (!isset($data['used_in_forms_disabled'])) {
                $data['used_in_forms'] = [static::USED_IN_FORMS];
            }

            // fix for required field validation
            if (isset($data['is_required']) && $data['is_required']) {
                $availableForms = $this->helper->getUsedInForms()[static::ENTITY_TYPE];
                $formsArr = array_column($availableForms, 'value');
                $formsArr[] = static::USED_IN_FORMS;
                $data['used_in_forms'] = $formsArr;
            }

            $defaultValueField = $model->getDefaultValueByInput($data['frontend_input']);
            if ($defaultValueField) {
                $scope = $this->getRequest()->getParam('website') ? 'scope_' : '';
                $data[$scope . 'default_value'] = $this->getRequest()->getParam($scope . $defaultValueField);
            }

            $data['entity_type_id'] = $this->entityTypeId;

            if (!isset($data['frontend_class'])) {
                $data['frontend_class'] = '';
            }

            $data['frontend_class'] = strtr($data['frontend_class'], [
                'sfm-select' => '',
                'sfm-checkbox-set' => '',
            ]);

            if (isset($data['frontend_display_type'])) {
                $data['frontend_class'] .= ' sfm-' . $data['frontend_display_type'];
            }

            $model->addData($data);

            $useDefaults = $this->getRequest()->getPost('use_default');
            if ($useDefaults) {
                foreach ($useDefaults as $key) {
                    $model->setData($key, null);
                }
            }

            try {
                $model->save();

                if (isset($this->quoteFactory)) {
                    $quote = $this->quoteFactory->create();
                    $quote->saveNewAttribute($model);
                }

                if (isset($this->orderFactory)) {
                    $order = $this->orderFactory->create();
                    $order->saveNewAttribute($model);
                }

                // save region_id when region is saved
                if ($model->getAttributeCode() == 'region') {
                    $relatedModel = $this->initModel();
                    $relatedModel->loadByCode($this->entityTypeId, 'region_id');

                    $relatedModel
                        ->setDefaultFrontendLabel($this->getRequest()->getParam('frontend_label'))
                        ->setSortOrder($model->getSortOrder())
                        ->save();
                }
                $this->messageManager->addSuccess(__('You saved the field.'));
                $this->_session->setFieldData(false);
                if ($this->getRequest()->getParam('back', false)) {
                    return $this->returnResult(
                        '*/*/edit',
                        ['attribute_id' => $model->getId(), '_current' => true]
                    );
                }

                return $this->returnResult('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_session->setFieldData($data);

                return $this->returnResult(
                    '*/*/edit',
                    ['attribute_id' => $fieldId, '_current' => true]
                );
            }
        }

        return $this->returnResult('*/*/');
    }

    /**
     * @param string $path
     * @param array $params
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    private function returnResult($path = '', array $params = [])
    {
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
            ->setPath($path, $params);
    }

    /**
     * @param array $data
     * @return void
     */
    private function preprocessOptionsData(&$data)
    {
        if (isset($data['serialized_options'])) {
            $serializedOptions = json_decode($data['serialized_options'], JSON_OBJECT_AS_ARRAY);
            foreach ($serializedOptions as $serializedOption) {
                $option = [];
                parse_str($serializedOption, $option);
                $data = array_replace_recursive($data, $option);
            }
        }
        unset($data['serialized_options']);
    }
}
