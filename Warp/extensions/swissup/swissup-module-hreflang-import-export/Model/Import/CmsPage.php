<?php

namespace Swissup\HreflangImportExport\Model\Import;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Swissup\Hreflang\Model\ResourceModel\Page as ResourcePage;

class CmsPage extends AbstractEntity
{
    private PageRepositoryInterface $pageRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private ResourcePage $resourcePage;

    private array $pages = [];

    public function __construct(
        PageRepositoryInterface $pageRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ResourcePage $resourcePage,
        Context $context
    ) {
        $this->pageRepository = $pageRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resourcePage = $resourcePage;

        $this->_dataSourceModel = $context->getImportData();
        $this->_importExportData = $context->getImportExportData();
        $this->_resourceHelper = $context->getResourceHelper();
        $this->errorAggregator = $context->getErrorAggregator();
        $this->jsonHelper = $context->getJsonHelper();

        $this->initMessageTemplates();
    }

    /**
     * Init Error Messages
     */
    protected function initMessageTemplates()
    {
        $this->addMessageTemplate(
            'PageIdRequired',
            __('Page ID is empty or invalid.')
        );
        $this->addMessageTemplate(
            'PageIdNotFound',
            __('Page ID not found.')
        );
        $this->addMessageTemplate(
            'HreflangPageIdRequired',
            __('Hreflang linked Page ID is empty or invalid.')
        );
        $this->addMessageTemplate(
            'HreflangPageIdNotFound',
            __('Hreflang linked Page ID not found.')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityTypeCode()
    {
        return 'swissup_hreflang_cms_pages';
    }

    /**
     * {@inheritdoc}
     */
    public function validateRow(
        array $rowData,
        $rowNum,
        bool $validatePageId = false
    ) {
        $pageId = (int)($rowData['page_id'] ?? '');
        if (empty($pageId)) {
            $this->addRowError('PageIdRequired', $rowNum);
        } elseif ($validatePageId && !$this->getPage($pageId)) {
            $this->addRowError('PageIdNotFound', $rowNum);
        }

        $hreflangPageIds = $this->prepareHreflangPageIds($rowData);
        foreach ($hreflangPageIds as $hreflangPageId) {
            if (empty($hreflangPageId)) {
                $this->addRowError('HreflangPageIdRequired', $rowNum);
                break;
            } elseif ($validatePageId && !$this->getPage($hreflangPageId)) {
                $this->addRowError('HreflangPageIdNotFound', $rowNum);
                break;
            }
        }

        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }

        $this->_validatedRows[$rowNum] = true;

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    private function prepareHreflangPageIds(array $row): array {
        $hreflangLinks = $row['hreflang_links'] ?? '';
        $hreflangPageIds = explode(',', $hreflangLinks);

        return array_map('intval', $hreflangPageIds ?: []);
    }

    protected function _importData(): bool
    {
        $behavior = $this->getBehavior();
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $this->preloadPages($bunch);
            $importRows = [];
            $validatePageId = true;


            foreach ($bunch as $rowNum => $row) {
                $isValid = $this->validateRow($row, $rowNum, $validatePageId);
                if (!$isValid) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);

                    continue;
                }

                $pageId = (int) $row['page_id'];
                if (Import::BEHAVIOR_DELETE === $behavior) {
                    $rowKey = $pageId;
                    $importRows[$rowKey] = $row;

                    $this->countItemsDeleted++;
                } else {
                    $page = $this->getPage($pageId);
                    $dbHreflangLinks = $this->resourcePage->getHreflangLinks($page);
                    $rowKey = implode(':', $row);
                    $importRows[$rowKey] = $row;

                    $this->countItemsCreated += (int) empty($dbHreflangLinks);
                    $this->countItemsUpdated += (int) !empty($dbHreflangLinks);
                }
            }

            if (empty($importRows)) {
                continue;
            }

            $table = $this->resourcePage->getMainTable();
            $connection = $this->resourcePage->getConnection();
            if (Import::BEHAVIOR_DELETE === $behavior
                || Import::BEHAVIOR_REPLACE === $behavior
            ) {
                $pageIds = array_column($importRows, 'page_id');
                $connection->delete($table, ['page_id IN (?)' => array_unique($pageIds)]);
            }

            if (Import::BEHAVIOR_REPLACE === $behavior
                || Import::BEHAVIOR_APPEND === $behavior
            ) {
                $newData = [];
                foreach ($importRows as $row) {
                    $pageId = $row['page_id'];
                    $hreflangPageIds = $this->prepareHreflangPageIds($row);
                    foreach ($hreflangPageIds as $hreflangPageId) {
                        $newData[] = [
                            'page_id' => $pageId,
                            'hreflang_page_id' => $hreflangPageId
                        ];
                    }
                }

                $fieldsOnUpdate = ['page_id', 'hreflang_page_id'];
                $connection->insertOnDuplicate($table, $newData, $fieldsOnUpdate);
            }
        }

        return true;
    }

    private function preloadPages(array $bunch): void
    {
        $candidates = [];
        foreach ($bunch as $row) {
            $candidates = array_merge(
                $candidates,
                [intval($row['page_id'])],
                $this->prepareHreflangPageIds($row)
            );
        }

        $candidates = array_unique($candidates);
        $criteria = $this->searchCriteriaBuilder
            ->addFilter('page_id', implode(',', $candidates), 'in')
            ->create();
        $pageList = $this->pageRepository->getList($criteria);
        $this->pages = $pageList->getItems();
    }

    private function getPage(int $pageId): ?PageInterface
    {
        $result = array_filter($this->pages, function ($page) use ($pageId) {
            return (int)$page->getId() === $pageId;
        });

        return count($result) ? reset($result) : null;
    }
}
