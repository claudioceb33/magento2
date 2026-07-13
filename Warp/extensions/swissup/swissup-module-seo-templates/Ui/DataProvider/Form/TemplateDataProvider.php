<?php

namespace Swissup\SeoTemplates\Ui\DataProvider\Form;

use Swissup\SeoTemplates\Model\Template;

class TemplateDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Swissup\Navigationpro\Model\ResourceModel\Menu\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface $request
     */
    private $request;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Swissup\SeoTemplates\Model\ResourceModel\Template\CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->collectionFactory = $collectionFactory;
        $this->request = $request;
        $this->registry = $registry;
    }

    public function getData()
    {
        $templateId = $this->request->getParam($this->getRequestFieldName(), false);
        $entityType = $this->request->getParam('entity_type', Template::ENTITY_TYPE_PRODUCT);
        $dataName = $this->request->getParam('data_name', Template::DATA_META_TITLE);

        if ($template = $this->registry->registry('seotemplates_template')) {
            return [
                    $template->getId() => $template->getData()
                ];
        } elseif ($templateId) {
            foreach ($this->collection->getItems() as $template) {
                return [
                    $template->getId() => $template->getData()
                ];
            }
        }

        return [
            null => [
                'entity_type' => $entityType,
                'data_name' => $dataName
            ]
        ];
    }
}
