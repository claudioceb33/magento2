<?php
declare(strict_types=1);

namespace Ecommerce66\Brands\Model\Branddetails;

use Ecommerce66\Brands\Model\ResourceModel\BrandDetails\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Store\Model\StoreManagerInterface;

class DataProvider extends AbstractDataProvider
{

    /**
     * @var \Ecommerce66\Brands\Model\ResourceModel\BrandDetails\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * DataProvider constructor.
     *
     * @param string                 $name
     * @param string                 $primaryFieldName
     * @param string                 $requestFieldName
     * @param CollectionFactory      $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param StoreManagerInterface  $storeManager
     * @param array                  $meta
     * @param array                  $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $mediaUrl = $this->storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        $this->prepareLoadedData($mediaUrl);
        /*$items = $this->collection->getItems();
        foreach ($items as $model) {
            $this->loadedData[$model->getId()] = $model->getData();
        }*/
        $data = $this->dataPersistor->get('ecommerce66_brands_brand_details');

        if (!empty($data)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data);
            $this->loadedData[$model->getId()] = $model->getData();
            $this->dataPersistor->clear('ecommerce66_brands_brand_details');
        }

        return $this->loadedData;
    }

    /**
     * @param string $mediaUrl
     */
    protected function prepareLoadedData($mediaUrl)
    {
        $items = $this->collection->getItems();
        foreach ($items as $model) {
            $data = $model->getData();

            $images = [
                'logo',
                'image',
                'mobile_image',
                'landing_image',
                'landing_mobile_image',
                'product_banner_img'
            ];

            foreach ($images as $image) {
                if (isset($data[$image])) {
                    $name = $data[$image];
                    unset($data[$image]);
                    $data[$image][0] = [
                        'name' => $name,
                        'url' => $mediaUrl.$name
                    ];
                }
            }

            $this->loadedData[$model->getId()] = $data;
        }
    }
}
