<?php
namespace Swissup\SeoHtmlSitemap\Ui\DataProvider;

use \Magento\Ui\DataProvider\AbstractDataProvider;
use \Swissup\SeoHtmlSitemap\Model\ResourceModel\Link\Collection;

class LinkDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Link's collection
     *
     * @var \Swissup\SeoHtmlSitemap\Model\ResourceModel\Link\Collection
     */
    protected $collection;
    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collection,
        array $addFieldStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collection;
        $this->addFieldStrategies = $addFieldStrategies;
    }
    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
        } else {
            parent::addField($field, $alias);
        }
    }
}
