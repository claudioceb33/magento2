<?php

namespace Swissup\Hreflang\Model\CurrentUrl;

use Magento\Framework\Registry;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Swissup\SeoCore\Model\CurrentUrl\ProviderInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class AttributepagesPageView implements ProviderInterface
{
    /**
     * @var string
     */
    protected $collectionClass;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var AbstractCollection
     */
    private $collection;

    /**
     * @param RequestInterface       $request
     * @param Registry               $registry
     * @param ObjectManagerInterface $objectManager
     * @param string                 $collectionClass
     */
    public function __construct(
        RequestInterface $request,
        Registry $registry,
        ObjectManagerInterface $objectManager,
        $collectionClass = 'Swissup\Attributepages\Model\ResourceModel\Entity\Collection'
    ) {
        $this->request = $request;
        $this->registry = $registry;
        $this->objectManager = $objectManager;
        $this->collectionClass = $collectionClass;
    }

    /**
     * {@inheritdoc}
     */
    public function provide(
        \Magento\Store\Model\Store $store,
        $queryParamsToUnset = []
    ) {
        $pathInfo = $this->request->getAlias(
            \Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS
        );
        $collection = $this->getCollection();
        $pages = $collection->walk(function ($item, $store) {
            $allowedSores = [Store::DEFAULT_STORE_ID, $store->getId()];
            if (array_intersect($allowedSores, $item->getStores())) {
                return $item;
            }

            return null;
        }, [$store]);

        $pages = array_filter($pages);
        if (!$pages) {
            return null;
        }

        $page = reset($pages);
        $parent = $this->getParentPage($store, $page);
        if ($parent && !$parent->getId()) {
            return null;
        }

        $newPathInfo = ($parent ? $parent->getIdentifier() . '/' : '') . $page->getIdentifier();
        $url = $store->getCurrentUrl(false, $queryParamsToUnset);
        return $pathInfo == $newPathInfo
            ? $url
            : str_replace($pathInfo, $newPathInfo, $url);
    }

    /**
     * @return AbstractCollection
     */
    private function getCollection()
    {
        if (!$this->collection) {
            $this->collection = $this->objectManager->create($this->collectionClass);
            $currentPage = $this->registry->registry('attributepages_current_page');
            $this->collection
                ->addFieldToFilter(
                    'attribute_id',
                    $currentPage->getAttributeId()
                )
                ->addFieldToFilter(
                    'option_id',
                    $currentPage->getOptionId() ? $currentPage->getOptionId() : ['null' => true]
                )
                ->addUseForAttributePageFilter();
        }

        return $this->collection;
    }

    /**
     * @param  Store  $store
     * @param  [type] $page
     * @return mixed
     */
    private function getParentPage(Store $store, $page)
    {
        if ($page->isAttributeBasedPage()) {
            return false;
        }

        $collection = $this->objectManager->create($this->collectionClass);
        $collection->addFieldToFilter('attribute_id', $page->getAttributeId())
            ->addStoreFilter($store)
            ->addAttributeOnlyFilter()
            ->addUseForAttributePageFilter();

        return $collection->getFirstItem();
    }
}
