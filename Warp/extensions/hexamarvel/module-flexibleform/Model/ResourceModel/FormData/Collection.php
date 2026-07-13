<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */
namespace Hexamarvel\FlexibleForm\Model\ResourceModel\FormData;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var primaryId
     */
    protected $_idFieldName = 'id';

    public function _construct()
    {
        $this->_init(
            \Hexamarvel\FlexibleForm\Model\FormData::class,
            \Hexamarvel\FlexibleForm\Model\ResourceModel\FormData::class
        );
    }
}
