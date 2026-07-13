<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */
namespace Hexamarvel\FlexibleForm\Ui\Component\Listing\Column;

class FieldCount extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * @var \Hexamarvel\FlexibleForm\Model\FieldFactory
     */
    protected $fieldFactory;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Hexamarvel\FlexibleForm\Model\FieldFactory $fieldFactory
     * @param array $components = []
     * @param array $data = []
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Hexamarvel\FlexibleForm\Model\FieldFactory $fieldFactory,
        array $components = [],
        array $data = []
    ) {
        $this->fieldFactory = $fieldFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array $dataSource
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $formId = $item['id'];
                $fields = $this->fieldFactory->create()->getCollection()->addFieldToFilter('form_id', $formId);
                $item[$this->getName()] = $fields->getSize();
            }
        }

        return $dataSource;
    }
}
