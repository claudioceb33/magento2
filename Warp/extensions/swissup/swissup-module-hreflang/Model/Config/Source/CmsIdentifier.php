<?php

namespace Swissup\Hreflang\Model\Config\Source;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;

class CmsIdentifier implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var CmsIdentifier\LabelBuilder
     */
    private $labelBuilder;

    /**
     * @param PageRepositoryInterface    $pageRepository
     * @param SearchCriteriaBuilder      $searchCriteriaBuilder
     * @param SortOrderBuilder           $sortOrderBuilder
     * @param CmsIdentifier\LabelBuilder $labelBuilder
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        CmsIdentifier\LabelBuilder $labelBuilder
    ) {
        $this->pageRepository = $pageRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->labelBuilder = $labelBuilder;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getPages();
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = [];
        foreach ($this->toOptionArray() as $item) {
            $result[$item['value']] = $item['label'];
        }
        return $result;
    }

    private function getPages(): array
    {
        $sortOrder = $this->sortOrderBuilder
            ->setField('identifier')
            ->setAscendingDirection()
            ->create();

        $pageNumber = 0;
        $pageSize = 50;
        $pages = [];
        do {
            $pageNumber++;
            $criteria = $this->searchCriteriaBuilder
                ->setCurrentPage($pageNumber)
                ->setPageSize($pageSize)
                ->addSortOrder($sortOrder)
                ->create();
            $pageList = $this->pageRepository->getList($criteria);

            foreach ($pageList->getItems() as $id => $page) {
                $label = $this->labelBuilder->build($page);
                $pages[$label] = [
                    'value' => $id,
                    'label' => $label,
                    'is_active' => $page->getIsActive()
                ];
            };
        } while ($pageList->getTotalCount() > $pageNumber * $pageSize);

        return $pages;
    }
}
