<?php

namespace Swissup\SeoCrossLinks\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class LinkActions extends Column
{
    const URL_PATH_EDIT = 'seocrosslinks/link/edit';
    const URL_PATH_DELETE = 'seocrosslinks/link/delete';
    const URL_PATH_ENABLE = 'seocrosslinks/link/enable';
    const URL_PATH_DISABLE = 'seocrosslinks/link/disable';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            if (!isset($item['link_id'])) {
                continue;
            }

            $item[$this->getData('name')] = [
                'edit' => [
                    'href' => $this->urlBuilder->getUrl(
                        static::URL_PATH_EDIT,
                        [
                            'link_id' => $item['link_id']
                        ]
                    ),
                    'label' => __('Edit')
                ],
                'delete' => [
                    'href' => $this->urlBuilder->getUrl(
                        static::URL_PATH_DELETE,
                        [
                            'link_id' => $item['link_id']
                        ]
                    ),
                    'label' => __('Delete'),
                    'confirm' => [
                        'title' => __('Delete'),
                        'message' => __('Are you sure you want to delete a record?')
                    ]
                ],
                'enable' => [
                    'href' => $this->urlBuilder->getUrl(
                        static::URL_PATH_ENABLE,
                        [
                            'link_id' => $item['link_id']
                        ]
                    ),
                    'label' => __('Enable'),
                    'confirm' => [
                        'title' => __('Enable'),
                    ]
                ],
                'disable' => [
                    'href' => $this->urlBuilder->getUrl(
                        static::URL_PATH_DISABLE,
                        [
                            'link_id' => $item['link_id']
                        ]
                    ),
                    'label' => __('Disable'),
                    'confirm' => [
                        'title' => __('Disable'),
                    ]
                ]
            ];
        }

        return $dataSource;
    }
}
