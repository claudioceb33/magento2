<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_Formbuilder
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\Formbuilder\Ui\Component\Listing\Column;

use Lof\Formbuilder\Helper\Data;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Lof\Formbuilder\Block\Adminhtml\Form\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\UrlInterface;

class FormatDate extends Column
{
    /**
     * @var UrlBuilder
     */
    protected UrlBuilder $actionUrlBuilder;

    /**
     * @var UrlInterface
     */
    protected UrlInterface $urlBuilder;

    /**
     * @var Data
     */
    protected Data $formatDate;

    /**
     * FormatDate constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlBuilder $actionUrlBuilder
     * @param UrlInterface $urlBuilder
     * @param Data $formatDate
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlBuilder $actionUrlBuilder,
        UrlInterface $urlBuilder,
        Data $formatDate,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->actionUrlBuilder = $actionUrlBuilder;
        $this->formatDate = $formatDate;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if ($name == 'creation_time') {
                    $getTime = $item[$name];
                    if (isset($item['creation_time'])) {
                        $item['creation_time'] = $this->formatDate->formatDateFormBuilder($getTime);
                    }
                }
                if ($name == 'update_time') {
                    $getTime = $item[$name];
                    if (isset($item['update_time'])) {
                        $item['update_time'] = $this->formatDate->formatDateFormBuilder($getTime);
                    }
                }
                if ($name == 'created_time') {
                    $getTime = $item[$name];
                    if (isset($item['created_time'])) {
                        $item['created_time'] = $this->formatDate->formatDateFormBuilder($getTime);
                    }
                }
                if ($name == 'updated_time') {
                    $getTime = $item[$name];
                    if (isset($item['updated_time'])) {
                        $item['updated_time'] = $this->formatDate->formatDateFormBuilder($getTime);
                    }
                }
            }
        }
        return $dataSource;
    }
}
