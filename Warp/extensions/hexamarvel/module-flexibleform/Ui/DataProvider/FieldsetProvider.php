<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Ui\DataProvider;

use Hexamarvel\FlexibleForm\Model\ResourceModel\FieldSet\CollectionFactory;

class FieldsetProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collection;


    /**
     * @var \Magento\Framework\App\Request\Http
    */
    protected $request;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $categoryFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $categoryFactory,
        \Magento\Framework\App\Request\Http $request,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $categoryFactory->create();
        $this->request = $request;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        $items = $this->collection->getItems();
        $data = [];
        foreach ($items as $fieldset) {
            $data[$fieldset->getId()]['fieldset_configuration'] = [
                'is_active' => $fieldset->getData('is_active'),
                'title' => $fieldset->getData('title'),
                'description' => $fieldset->getData('description'),
                'position' => $fieldset->getData('position'),
                'class' => $fieldset->getData('class'),
                'id' => $this->request->getParam('fieldset_id')
            ];
        }

        if (!empty($data)) {
            return $data;
        }
    }
}
