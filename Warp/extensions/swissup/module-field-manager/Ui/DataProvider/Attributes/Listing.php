<?php
namespace Swissup\FieldManager\Ui\DataProvider\Attributes;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Swissup\FieldManager\Helper\BackendGrid;
use Swissup\FieldManager\Model\Config\Source\Status;
use Swissup\FieldManager\Model\ResourceModel\Customer\Form\Attribute\Collection;

class Listing extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    private $websiteKey;
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var BackendGrid
     */
    private $gridHelper;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Collection $collection
     * @param StoreManagerInterface $storeManager
     * @param BackendGrid $gridHelper
     * @param UrlInterface $urlBuilder
     * @param array $meta
     * @param array $data
     * @internal param RequestInterface $request
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collection,
        StoreManagerInterface $storeManager,
        BackendGrid $gridHelper,
        UrlInterface $urlBuilder,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collection;
        $this->storeManager = $storeManager;
        $this->gridHelper = $gridHelper;
        $this->urlBuilder = $urlBuilder;
        $this->websiteKey = $data['config']['website_key'];
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        /** @var Collection $collection */
        $collection = $this->getCollection();

        if ($store = $this->getStore()) {
            $collection->setStore($store);
        }

        $items = [];
        foreach ($collection as $attribute) {
            $values = $attribute->toArray();
            $values['status'] = $this->getAttributeStatus($attribute);

            switch ($attribute->getAttributeCode()) {
                case 'firstname':
                case 'lastname':
                    $values['comment'] = __('Name must be required.');
                    break;
                case 'country_id':
                    $values['comment'] = __('Country must be visible to keep Magento working.');
                    break;
                case 'region':
                    $values['comment'] = __('Use "Stores > Configuration > General > State" to change region field status');
                    break;
                case 'postcode':
                    $values['comment'] = __('Use "Stores > Configuration > General > Country Options" to change postcode status');
                    break;
                case 'email':
                    $values['comment'] = __('Email must be required.');
                    break;
                case 'website_id':
                    $values['comment'] = __('Website_id must be required.');
                    break;
                case 'group_id':
                    $values['comment'] = __('Group_id must be required.');
                    break;
            }

            $items[] = $values;
        }

        return [
            'totalRecords' => $this->collection->getSize(),
            'items' => $items
        ];
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        if ($this->isScopeUsed()) {
            $meta['listing_top']['children']['listing_massaction']['children']
                ['status']['arguments']['actions'][3] = [
                    'type'  => 'delete',
                    'label' => __('Remove website overrides'),
                    'url'   => $this->urlBuilder->getUrl('*/*/massStatus', [
                        'status' => 'delete'
                    ]),
                ];
        }

        return $meta;
    }

    /**
     * @param \Magento\Eav\Model\Attribute $attribute
     */
    private function getAttributeStatus($attribute)
    {
        $isVisible  = $attribute->getData('is_visible');
        $isRequired = $attribute->getData('is_required');

        if ($this->isScopeUsed()) {
            $isVisible  = $attribute->getIsVisible();
            $isRequired = $attribute->getIsRequired();
        }

        if (!$isVisible) {
            return Status::HIDDEN;
        } elseif ($isRequired) {
            return Status::REQUIRED;
        }

        return Status::OPTIONAL;
    }

    /**
     * @return bool|\Magento\Store\Model\Store
     */
    private function getStore()
    {
        $store = false;
        if ($websiteId = $this->gridHelper->getCurrentWebsiteId($this->websiteKey)) {
            /** @var \Magento\Store\Model\Website $website */
            $website = $this->storeManager->getWebsite($websiteId);
            $store = $website->getDefaultStore();
        }

        return $store;
    }

    /**
     * @return bool
     */
    private function isScopeUsed()
    {
        return (bool) $this->gridHelper->getCurrentWebsiteId($this->websiteKey);
    }
}
