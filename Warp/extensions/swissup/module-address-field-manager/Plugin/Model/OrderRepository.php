<?php
namespace Swissup\AddressFieldManager\Plugin\Model;

use Magento\Sales\Model\Order\Address;
use Swissup\AddressFieldManager\Api\Data\FieldDataInterfaceFactory;
use Swissup\AddressFieldManager\Model\ResourceModel\Address\OrderFactory;

class OrderRepository
{
    /**
     * @var \Swissup\FieldManager\Helper\Data
     */
    protected $helper;

    /**
     * @var \Swissup\AddressFieldManager\Model\ResourceModel\Address\Order
     */
    protected $orderAddressModel;

    /**
     * Field data factory
     * @var FieldDataInterfaceFactory
     */
    protected $fieldDataFactory;

    /**
     * @param \Swissup\FieldManager\Helper\Data $helper
     * @param OrderFactory $orderAddressFactory
     * @param FieldDataInterfaceFactory $fieldDataFactory
     */
    public function __construct(
        \Swissup\FieldManager\Helper\Data $helper,
        OrderFactory $orderAddressFactory,
        FieldDataInterfaceFactory $fieldDataFactory
    ) {
        $this->helper = $helper;
        $this->orderAddressModel = $orderAddressFactory->create();
        $this->fieldDataFactory = $fieldDataFactory;
    }

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface $entity
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function afterGet(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $entity
    ) {
        $this->setAddressFields($entity);

        return $entity;
    }

    /**
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderSearchResultInterface $searchResult
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface
     */
    public function afterGetList(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderSearchResultInterface $searchResult
    ) {
        foreach ($searchResult->getItems() as $order) {
            $this->setAddressFields($order);
        }

        return $searchResult;
    }

    /**
     * Add address fields values to order
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return void
     */
    private function setAddressFields(
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        $billingAddressId = $order->getBillingAddressId();
        $shippingAddressId = $order->getShippingAddressId();
        $customItems = $this->orderAddressModel
            ->loadByIds([$billingAddressId, $shippingAddressId]);

        if (empty($customItems)) {
            return;
        }

        $fieldsData = [];
        $attributes = $this->helper->getEntityAttributes();
        foreach ($customItems as $item) {
            $addressType = $item['entity_id'] == $billingAddressId ?
                Address::TYPE_BILLING : Address::TYPE_SHIPPING;
            unset($item['entity_id']);

            foreach ($item as $code => $value) {
                if ($value === null) {
                    continue;
                }

                $attribute = $attributes[$code];
                $fieldDataObject = $this->fieldDataFactory->create();
                $fieldDataObject->setAddressType($addressType);
                $fieldDataObject->setAttributeCode($code);
                $fieldDataObject->setLabel($attribute->getDefaultFrontendLabel());

                if ($attribute->usesSource()) {
                    $value = $attribute->getSource()->getOptionText($value);
                }
                $fieldDataObject->setValue($value);

                $fieldsData[] = $fieldDataObject;
            }
        }

        if (!empty($fieldsData)) {
            $extensionAttributes = $order->getExtensionAttributes();
            $extensionAttributes->setSwissupAddressFields($fieldsData);
            $order->setExtensionAttributes($extensionAttributes);
        }
    }
}
