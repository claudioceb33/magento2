<?php
namespace Swissup\SeoHtmlSitemap\Ui\DataProvider\Form;

use \Swissup\SeoHtmlSitemap\Model\ResourceModel\Link\Collection;
use \Swissup\SeoHtmlSitemap\Model\Link\Locator\LocatorInterface;

class LinkDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Swissup\SeoHtmlSitemap\Model\Link\Locator\LocatorInterface
     */
    protected $locator;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collection,
        LocatorInterface $locator,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collection;
        $this->locator = $locator;
    }

    public function getData()
    {
        $link = $this->locator->getLink();
        if (!$link->getId()) {
            return [];
        }

        return [
            $link->getId() => $link->getData()
        ];
    }
}
