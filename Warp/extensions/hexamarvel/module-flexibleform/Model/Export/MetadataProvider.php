<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */
namespace Hexamarvel\FlexibleForm\Model\Export;

use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MetadataProvider extends \Magento\Ui\Model\Export\MetadataProvider
{
    /**
     * @param Filter $filter
     * @param TimezoneInterface $localeDate
     * @param ResolverInterface $localeResolver
     * @param string $dateFormat
     * @param array $data
     */
    public function __construct(
        Filter $filter,
        TimezoneInterface $localeDate,
        ResolverInterface $localeResolver,
        $dateFormat = 'M j, Y H:i:s A',
        array $data = []
    ) {
        parent::__construct($filter, $localeDate, $localeResolver, $dateFormat, $data);
    }

    /**
     * Returns row data
     *
     * @param DocumentInterface $document
     * @param array $fields
     * @param array $options
     * @return array
     */
    public function getRowData(DocumentInterface $document, $fields, $options): array
    {
        $row = [];
        foreach ($fields as $column) {
            if (isset($options[$column])) {
                $key = $document->getCustomAttribute($column)->getValue();
                if (isset($options[$column][$key])) {
                    $row[] = $options[$column][$key];
                } else {
                    $row[] = '';
                }
            } else {
                if ($column != 'field_count' && $column != 'result_count' && $column != 'stores') {
                    $row[] = $document->getCustomAttribute($column)->getValue();
                }
            }
        }
        return $row;
    }

     /**
      * Retrieve Headers row array for Export
      *
      * @param UiComponentInterface $component
      * @return string[]
      */
    public function getHeaders(UiComponentInterface $component): array
    {
        $row = [];
        foreach ($this->getColumns($component) as $column) {
            if ($column->getData('name') != 'field_count' &&
            $column->getData('name') != 'result_count' &&
            $column->getData('name') != 'stores') {
                $row[] = $column->getData('config/label');
            }
        }

        return $row;
    }
}
