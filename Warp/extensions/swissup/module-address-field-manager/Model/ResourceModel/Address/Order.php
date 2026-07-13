<?php
namespace Swissup\AddressFieldManager\Model\ResourceModel\Address;

class Order extends \Swissup\FieldManager\Model\ResourceModel\AbstractSales
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Address
     */
    protected $parentResourceModel;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Swissup\FieldManager\Helper\Data $helper
     * @param \Magento\Sales\Model\ResourceModel\Order\Address $parentResourceModel
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Swissup\FieldManager\Helper\Data $helper,
        \Magento\Sales\Model\ResourceModel\Order\Address $parentResourceModel,
        $connectionName = null
    ) {
        $this->parentResourceModel = $parentResourceModel;
        parent::__construct($context, $helper, $connectionName);
    }

    /**
     * Initializes resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_afm_order_address', 'entity_id');
    }

    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\Address
     */
    protected function getParentResourceModel()
    {
        return $this->parentResourceModel;
    }
}
