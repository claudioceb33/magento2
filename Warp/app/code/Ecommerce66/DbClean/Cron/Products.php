<?php

namespace Ecommerce66\DbClean\Cron;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductRepository;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;

class Products
{
    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var AppState
     */
    protected $appState;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Products constructor.
     *
     * @param ProductCollectionFactory   $productCollectionFactory
     * @param ProductRepositoryInterface $productRepository
     * @param LoggerInterface            $logger
     * @param ScopeConfigInterface       $scopeConfig
     * @param DateTime                   $date
     * @param AppState                   $appState
     * @param Registry                   $registry
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        DateTime $date,
        AppState $appState,
        Registry $registry
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->date = $date;
        $this->appState = $appState;
        $this->registry = $registry;


        //$this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        try {
            $this->appState->setAreaCode('adminhtml');
        } catch (LocalizedException $e) {
            $e->getMessage();
        }
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $cronEnabled = (int)$this->scopeConfig->getValue(
            'dbclean/products/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (!$cronEnabled) {
            $this->logger->info('Old products delete cron is disabled.');
            return $this;
        }

        $this->appState->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_GLOBAL,
            function ()  {
                $this->registry->register('isSecureArea', true);
                $this->deleteOldProducts();
                //$this->deleteOldProducts('configurable');
            }
        );

        return $this;
    }

    /**
     * @param string $type
     *
     * @throws LocalizedException
     */
    protected function deleteOldProducts($type = 'simple')
    {
        $hasOptions = $type == 'simple' ? 0 : 1;
        $dateLimit = $this->date->gmtDate('Y-m-d H:i:s', strtotime('-365 days'));
        $collection = $this->productCollectionFactory->create();
        $collection->joinField(
            'qty',
            'inventory_source_item',
            'quantity',
            'sku=sku',
            '{{table}}.quantity < 1 AND {{table}}.status = 0'
        );

        $collection->addAttributeToFilter('updated_at', ['lt' => $dateLimit])
            ->addAttributeToFilter('type_id', ['eq' => $type])
            ->addAttributeToFilter('has_options', ['eq' => $hasOptions])
            ->setPageSize(1000)
            ->setOrder('updated_at', 'ASC');

        foreach ($collection as $product) {
            //echo $product->getId() . ' - ' . $product->getUpdatedAt() . ' ' . $product->getTypeId() . "\n";
            try {
                //$pc = $this->productRepository->getById($product->getId());
                $this->productRepository->delete($product);
            } catch (\Exception $e) {
                $this->logger->error('Error during database cleaning: ' . $e->getMessage());
                $this->logger->error('Error during database cleaning: ' . $e->getTraceAsString());
            }
        }
    }
}
