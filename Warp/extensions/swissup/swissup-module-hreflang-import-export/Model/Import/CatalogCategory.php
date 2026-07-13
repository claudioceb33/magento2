<?php

namespace Swissup\HreflangImportExport\Model\Import;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Swissup\Hreflang\Model\ResourceModel\Category as ResourceCategory;

class CatalogCategory  extends AbstractEntity
{
    private CategoryListInterface $categoryList;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private ResourceCategory $resourceCategory;

    private array $categories = [];

    public function __construct(
        CategoryListInterface $categoryList,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ResourceCategory $resourceCategory,
        Context $context
    ) {
        $this->categoryList = $categoryList;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resourceCategory = $resourceCategory;

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
            'CategoryIdRequired',
            __('Category ID is empty or invalid.')
        );
        $this->addMessageTemplate(
            'CategoryIdNotFound',
            __('Category ID not found.')
        );
        $this->addMessageTemplate(
            'HreflangCategoryIdRequired',
            __('Hreflang linked category ID is empty or invalid.')
        );
        $this->addMessageTemplate(
            'HreflangCategoryIdNotFound',
            __('Hreflang linked category ID not found.')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityTypeCode()
    {
        return 'swissup_hreflang_catalog_categories';
    }

    /**
     * {@inheritdoc}
     */
    public function validateRow(
        array $rowData,
        $rowNum,
        bool $validateCategoryId = false
    ) {
        $categoryId = (int)($rowData['category_id'] ?? '');
        if (empty($categoryId)) {
            $this->addRowError('CategoryIdRequired', $rowNum);
        } elseif ($validateCategoryId && !$this->getCategory($categoryId)) {
            $this->addRowError('CategoryIdNotFound', $rowNum);
        }

        $hreflangCategoryIds = $this->prepareHreflangCategoryIds($rowData);
        foreach ($hreflangCategoryIds as $hreflangCategoryId) {
            if (empty($hreflangCategoryId)) {
                $this->addRowError('HreflangCategoryIdRequired', $rowNum);
                break;
            } elseif ($validateCategoryId && !$this->getCategory($hreflangCategoryId)) {
                $this->addRowError('HreflangCategoryIdNotFound', $rowNum);
                break;
            }
        }

        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }

        $this->_validatedRows[$rowNum] = true;

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    private function prepareHreflangCategoryIds(array $row): array {
        $hreflangLinks = $row['hreflang_links'] ?? '';
        $hreflangCategoryIds = explode(',', $hreflangLinks);

        return array_map('intval', $hreflangCategoryIds ?: []);
    }

    protected function _importData(): bool
    {
        $behavior = $this->getBehavior();
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $this->preloadCategories($bunch);
            $importRows = [];
            $validateCategoryId = true;

            foreach ($bunch as $rowNum => $row) {
                $isValid = $this->validateRow($row, $rowNum, $validateCategoryId);
                if (!$isValid) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);

                    continue;
                }

                $categoryId = intval($row['category_id']);
                if (Import::BEHAVIOR_DELETE === $behavior) {
                    $rowKey = $categoryId;
                    $importRows[$rowKey] = $row;

                    $this->countItemsDeleted++;
                } else {
                    $rowKey = implode(':', $row);
                    $importRows[$rowKey] = $row;
                    $category = $this->getCategory($categoryId);
                    $dbHreflangLinks = $this->resourceCategory->getHreflangLinks($category);

                    $this->countItemsCreated += (int) empty($dbHreflangLinks);
                    $this->countItemsUpdated += (int) !empty($dbHreflangLinks);
                }
            }

            if (empty($importRows)) {
                continue;
            }

            $table = $this->resourceCategory->getMainTable();
            $connection = $this->resourceCategory->getConnection();
            if (Import::BEHAVIOR_DELETE === $behavior
                || Import::BEHAVIOR_REPLACE === $behavior
            ) {
                $categoryIds = array_column($importRows, 'category_id');
                $connection->delete($table, ['category_id IN (?)' => array_unique($categoryIds)]);
            }

            if (Import::BEHAVIOR_REPLACE === $behavior
                || Import::BEHAVIOR_APPEND === $behavior
            ) {
                $newData = [];
                foreach ($importRows as $row) {
                    $categoryId = $row['category_id'];
                    $hreflangCategoryIds = $this->prepareHreflangCategoryIds($row);
                    foreach ($hreflangCategoryIds as $hreflangCategoryId) {
                        $newData[] = [
                            'category_id' => $categoryId,
                            'hreflang_category_id' => $hreflangCategoryId
                        ];
                    }
                }

                $fieldsOnUpdate = ['category_id', 'hreflang_category_id'];
                $connection->insertOnDuplicate($table, $newData, $fieldsOnUpdate);
            }
        }

        return true;
    }

    private function preloadCategories(array $bunch): void
    {
        $candidates = [];
        foreach ($bunch as $row) {
            $candidates = array_merge(
                $candidates,
                [intval($row['category_id'])],
                $this->prepareHreflangCategoryIds($row)
            );
        }

        $candidates = array_unique($candidates);
        $criteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', implode(',', $candidates), 'in')
            ->create();
        $categoryList = $this->categoryList->getList($criteria);
        $this->categories = $categoryList->getItems();
    }

    private function getCategory(int $categoryId): ?CategoryInterface
    {
        $result = array_filter($this->categories, function ($category) use ($categoryId) {
            return (int)$category->getId() === $categoryId;
        });

        return count($result) ? reset($result) : null;
    }
}
