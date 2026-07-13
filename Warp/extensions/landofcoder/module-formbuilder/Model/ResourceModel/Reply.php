<?php /**
       * Landofcoder
       *
       * NOTICE OF LICENSE
       *
       * This source file is subject to the Landofcoder.com license that is
       * available through the world-wide-web at this URL:
       * https://landofcoder.com/terms
       *
       * DISCLAIMER
       *
       * Do not edit or add to this file if you wish to upgrade this extension to newer
       * version in the future.
       *
       * @category   Landofcoder
       * @package    Lof_Formbuilder
       * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
       * @license    https://landofcoder.com/terms
       */

namespace Lof\Formbuilder\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManagerInterface;

class Reply extends AbstractDb
{
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param $connectionName
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('lof_formbuilder_reply', 'reply_id');
    }

    /**
     * Load an object using 'identifier' field if there's no field specified and value is not numeric
     *
     * @param AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    public function load(AbstractModel $object, $value, $field = null): static
    {
        return parent::load($object, $value, $field);
    }
}
