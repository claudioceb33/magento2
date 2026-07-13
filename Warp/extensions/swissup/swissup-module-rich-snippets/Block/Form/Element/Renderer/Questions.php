<?php

namespace Swissup\RichSnippets\Block\Form\Element\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Element\Template;

class Questions extends Template
{
    protected $_template = 'dynamic-rows/questions.phtml';

    /**
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    private function getUiComponentTypes(): array
    {
        return [
            'record.textarea' => [
                'component' => 'Magento_Ui/js/form/element/textarea',
                'provider' => "{$this->getScopeName()}_data_source",
                'template' => 'ui/form/field'
            ]
        ];
    }

    private function getUiDataSource(AbstractElement $element): array
    {
        return [
            'component' => 'Magento_Ui/js/form/provider',
            'config' => [
                'data' => [
                    'questions' => json_decode($element->getValue() ?: '{}', true)
                ]
            ]
        ];
    }

    private function getUiComponentDynamicRowsTextarea(
        string $name,
        int $sortOrder,
        int $rows = 2
    ): array {
        return [
            'dataScope' => $name,
            'name' => $name,
            'type' => 'record.textarea',
            'config' => [
                'additionalClasses' => 'col-' . $name,
                'dataType' => 'text',
                'placeholder' =>  __(ucfirst($name)),
                'formElement' => 'text',
                'rows' => $rows,
                'sortOrder' => $sortOrder
            ]
        ];
    }

    private function getUiComponentDynamicRowsDeleteButton(): array
    {
        return [
            'name' => 'actionDelete',
            'dataScope' => 'actionDelete',
            'config' => [
                'component' => 'Magento_Ui/js/dynamic-rows/action-delete',
                'template' => 'Magento_Backend/dynamic-rows/cells/action-delete',
                'componentType' => 'actionDelete',
                'dataType' => 'text',
                'fit' => false,
                'label' => __('Actions'),
                'additionalClasses' => 'data-grid-actions-cell'
            ]
        ];
    }

    private function getUiComponentDynamicRowsRecord() : array
    {
        return [
            'type' => 'container',
            'name' => 'record',
            'children' =>  [
                'question' => $this->getUiComponentDynamicRowsTextarea('question', 20),
                'answer' => $this->getUiComponentDynamicRowsTextarea('answer', 30, 4),
                'actionDelete' => $this->getUiComponentDynamicRowsDeleteButton()
            ],
            'dataScope' => 'record',
            'config' => [
                'component' => 'Magento_Ui/js/dynamic-rows/record',
                'label' => 'Dynamic Rows',
                'isTemplate' => true,
                'is_collection' => true
            ]
        ];
    }

    /**
     * Prepare wysiwyg element HTML
     *
     * @param AbstractElement $element Form Element
     * @return AbstractElement
     */
    public function prepareElementHtml(AbstractElement $element)
    {
        // Initialize JS layout
        $scope = $this->getScopeName();
        $name = 'questions';
        $this->jsLayout = [
            'types' => $this->getUiComponentTypes(),
            'components' => [
                "{$scope}_data_source" => $this->getUiDataSource($element),
                "{$scope}" => [
                    'component' => 'uiComponent',
                    'children' => [
                        $name => [
                            'name' => $name,
                            'dataScope' => 'data',
                            'config' => [
                                'addButtonLabel' => __('New Question'),
                                'columnsHeader' => false,
                                'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows',
                                'componentType' => 'dynamicRows',
                                'deleteProperty' => false,
                                'deleteButtonLabel' => __('Delete'),
                                'parameterName' => $element->getName(),
                                'provider' => "{$scope}_data_source",
                                'deps' => "{$scope}_data_source",
                                'recordTemplate' => 'record',
                                'template' => 'Swissup_RichSnippets/dynamic-rows',
                                'dndConfig' => [
                                    'enabled' => false
                                ]
                            ],
                            'children' => [
                                'record' => $this->getUiComponentDynamicRowsRecord()
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $element->setData('after_element_html', $this->toHtml());
        $element->setValue('');

        return $element;
    }
}
