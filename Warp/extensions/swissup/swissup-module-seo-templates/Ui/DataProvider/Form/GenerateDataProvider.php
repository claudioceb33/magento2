<?php

namespace Swissup\SeoTemplates\Ui\DataProvider\Form;

use Swissup\SeoTemplates\Model\Template;

class GenerateDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Swissup\Navigationpro\Model\ResourceModel\Menu\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface $request
     */
    private $request;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Swissup\SeoTemplates\Model\ResourceModel\Template\CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->collectionFactory = $collectionFactory;
    }

    public function getData()
    {

        return [
            null => [
                // 'entity_type' => 1001,
                'page_size' => 100
            ]
        ];
    }
}
