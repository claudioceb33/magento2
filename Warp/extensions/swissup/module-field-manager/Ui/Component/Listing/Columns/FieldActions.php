<?php
namespace Swissup\FieldManager\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class FieldActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    /** Url path */
    protected $urlPathEdit;
    protected $urlPathDelete;

    /** @var UrlInterface */
    protected $urlBuilder;

    /**
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
        $this->urlPathEdit = $data['config']['urlPathEdit'];
        $this->urlPathDelete = $data['config']['urlPathDelete'];
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
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $frontend_label = $this->getEscaper()
                    ? $this->getEscaper()->escapeHtmlAttr($item['frontend_label'])
                    : $item['frontend_label'];
                if (isset($item['attribute_id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                $this->urlPathEdit,
                                [
                                    'attribute_id' => $item['attribute_id']
                                ]
                            ),
                            'label' => __('Edit')
                        ]
                    ];
                    if ($item['is_user_defined']) {
                        $item[$this->getData('name')]['delete'] = [
                            'href' => $this->urlBuilder->getUrl(
                                $this->urlPathDelete,
                                [
                                    'attribute_id' => $item['attribute_id']
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete "%1"', $frontend_label),
                                'message' => __('Are you sure you want to delete a %1 record?', $frontend_label)
                            ]
                        ];
                    }
                }
            }
        }

        return $dataSource;
    }
}
