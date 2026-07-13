<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Ui\Component\Listing\Column;

class Actions extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlInterface;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_urlInterface = $urlInterface;
    }

    /**
     * @param array $dataArray
     * @return array
     */
    public function prepareDataSource(array $dataArray)
    {
        if (isset($dataArray['data']['items'])) {
            foreach ($dataArray['data']['items'] as &$value) {
                $value[$this->getData('name')]['edit'] = $this->getLinkArray($value);
            }
        }

        return $dataArray;
    }

    /**
     * @param array $item
     * @param array
     */
    private function getLinkArray($item)
    {
        return [
            'href' => $this->_urlInterface->getUrl(
                'hexaform/form/edit',
                ['id' => $item['id']]
            ),
            'label' => __('Edit'),
            'hidden' => false,
        ];
    }
}
