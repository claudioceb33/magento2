<?php

namespace Swissup\SeoUrls\Model\Layer;

class PredefinedFilters extends \Magento\Framework\DataObject
{
    /**
     * @var \Magento\Framework\DataObject\Factory
     */
    protected $dataObjectFactory;

    /**
     * Construct
     *
     * @param \Magento\Framework\DataObject\Factory              $dataObjectFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Module\Manager                  $moduleManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\DataObject\Factory $dataObjectFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        parent::__construct($data);

        // define category filter
        if (!$this->hasData('category_filter')) {
            // value 'cat' is hardcoded in \Magento\CatalogSearch\Model\Layer\Filter\Category
            $this->setData(
                'category_filter',
                $this->dataObjectFactory->create([
                    'request_var' => 'cat',
                    'store_label' => $scopeConfig->getValue(
                        'swissup_seourls/layered_navigation/category_label',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                ])
            );
        }

        if ($moduleManager->isOutputEnabled('Swissup_Ajaxlayerednavigation')) {
            $this->initSwissupAjaxlayerednavigationFilters();
        }
    }

    /**
     * @param  array &$data
     */
    private function initSwissupAjaxlayerednavigationFilters()
    {
        // define stock filter
        if (!$this->hasData('stock_filter')) {
            // value 'in-stock' is hardcoded in \Swissup\Ajaxlayerednavigation\Model\Layer\Filter\Stock
            $this->setData(
                'stock_filter',
                $this->dataObjectFactory->create([
                    'request_var' => 'in-stock',
                    'store_label' => __('Stock'),
                    'attribute_code' => 'quantity_and_stock_status'
                ])
            );
        }

        // define rating filter
        if (!$this->hasData('rating_filter')) {
            // value 'rating' is hardcoded in \Swissup\Ajaxlayerednavigation\Model\Layer\Filter\Rating
            $this->setData(
                'rating_filter',
                $this->dataObjectFactory->create([
                    'request_var' => 'rating',
                    'store_label' => __('Rating'),
                    'attribute_code' => 'swissup_rating_summary'
                ])
            );
        }
    }
}
