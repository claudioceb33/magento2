<?php
namespace Hexamarvel\FlexibleForm\Ui\Component\Listing\Column;

class FormData extends \Magento\Ui\Component\Listing\Columns\Column
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
        \Hexamarvel\FlexibleForm\Model\FormDataFactory $formDataFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->fieldFactory = $fieldFactory;
        $this->formDataFactory = $formDataFactory;
        $this->urlBuilder = $urlBuilder;
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
                $fields = $this->fieldFactory->create()->getCollection()->addFieldToFilter('form_id', $item['id']);
                $fieldCount = $this->formDataFactory->create()->getCollection()->addFieldToFilter('form_id', $item['id'])->getSize();
                $item[$this->getName()] = '<a href="'.$this->urlBuilder->getUrl('hexaform/formdata/index', ['form_id' => $item['id']]).'" target="_blank">View Result</a> ['.$fieldCount.']';
            }
        }

        return $dataSource;
    }
}
