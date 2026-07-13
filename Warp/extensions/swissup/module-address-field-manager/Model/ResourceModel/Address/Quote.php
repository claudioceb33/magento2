<?php
namespace Swissup\AddressFieldManager\Model\ResourceModel\Address;

class Quote extends \Swissup\FieldManager\Model\ResourceModel\AbstractSales
{
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Address
     */
    protected $parentResourceModel;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Swissup\FieldManager\Helper\Data $helper
     * @param \Magento\Quote\Model\ResourceModel\Quote\Address $parentResourceModel
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Swissup\FieldManager\Helper\Data $helper,
        \Magento\Quote\Model\ResourceModel\Quote\Address $parentResourceModel,
        $connectionName = null
    ) {
        $this->parentResourceModel = $parentResourceModel;
        parent::__construct($context, $helper, $connectionName);
    }

    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_afm_quote_address', 'entity_id');
    }

    /**
     * @return \Magento\Quote\Model\ResourceModel\Quote\Address
     */
    protected function getParentResourceModel()
    {
        return $this->parentResourceModel;
    }
}
