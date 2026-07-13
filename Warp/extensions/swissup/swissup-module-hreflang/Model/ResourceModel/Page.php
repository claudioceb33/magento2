<?php

namespace Swissup\Hreflang\Model\ResourceModel;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\Store;

class Page extends AbstractDb
{
    const DATA_KEY_LINKS = 'hreflang_links';
    const DATA_KEY_PAGES = 'hreflang_pages';
    const DATA_KEY_IDENT = 'hreflang_identifiers';

    /**
     * {@inheritdoc}
     */
    protected $_isPkAutoIncrement = false;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        PageRepositoryInterface $pageRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        $this->pageRepository = $pageRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($context, $connectionName);
    }

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_hreflang_cms', 'page_id');
    }

    /**
     * @param  PageInterface $page
     * @return $this
     */
    public function loadHreflangData(PageInterface $page): self
    {
        $table = $this->getMainTable();
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($table, ['hreflang_page_id'])
            ->where('page_id = ?', $page->getId());
        $page->setData(self::DATA_KEY_LINKS, $connection->fetchCol($select) ?: []);

        return $this;
    }

    /**
     * @param PageInterface $page
     * @return $this
     */
    public function getHreflangLinks(PageInterface $page): array
    {
        if (!$page->hasData(self::DATA_KEY_LINKS)) {
            $this->loadHreflangData($page);
        }

        return $page->getData(self::DATA_KEY_LINKS);
    }

    /**
     * @param  PageInterface $page
     * @return $this
     */
    public function saveHreflangData(PageInterface $page): self
    {
        $links = $page->getData(self::DATA_KEY_LINKS);
        if (!is_array($links)) {
            return $this;
        }

        $table = $this->getMainTable();
        $connection = $this->getConnection();
        $connection->delete($table, ['page_id = ?' => $page->getId()]);

        $data = [];
        foreach ($links as $linkedPageId) {
            $data[$linkedPageId] = [
                'page_id' => $page->getId(),
                'hreflang_page_id' => $linkedPageId
            ];
        }

        if ($data) {
            $connection->insertMultiple($table, $data);
        }

        return $this;
    }

    /**
     * @param  PageInterface $page
     * @return array
     */
    public function getHreflangPages(PageInterface $page): array
    {
        if (!$page->hasData(self::DATA_KEY_PAGES)) {
            $items = [];
            if ($links = $this->getHreflangLinks($page)) {
                $criteria = $this->searchCriteriaBuilder
                    ->addFilter('page_id', implode(',', $links), 'in')
                    ->addFilter('is_active', '1', 'eq')
                    ->create();
                $pageList = $this->pageRepository->getList($criteria);
                $items = $pageList->getItems();
            }

            $page->setData(self::DATA_KEY_PAGES, $items);
        }

        return $page->getData(self::DATA_KEY_PAGES);
    }

    /**
     * @param  PageInterface $page
     * @return array
     */
    public function getHreflangIdentifiers(PageInterface $page): array
    {
        if (!$page->hasData(self::DATA_KEY_IDENT)) {
            $identifiers = [];

            foreach ($this->getHreflangPages($page) as $item) {
                foreach ($item->getStores() as $storeId) {
                    $identifiers[(int)$storeId] = $item->getIdentifier();
                }
            }

            $page->setData(self::DATA_KEY_IDENT, $identifiers);
        }

        return $page->getData(self::DATA_KEY_IDENT);
    }

    /**
     * @param  PageInterface $page
     * @param  Store|int     $store
     * @return string
     */
    public function getHreflangIdentifier(PageInterface $page, $store): string
    {
        $identifiers = $this->getHreflangIdentifiers($page);
        $storeId = is_object($store) ? $store->getId() : intval($store);

        return $identifiers[$storeId] ??
            ($identifiers[Store::DEFAULT_STORE_ID] ?? '');
    }
}
