<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */
namespace Hexamarvel\FlexibleForm\Model;

class FieldSet extends \Magento\Framework\Model\AbstractModel
{
    public function _construct()
    {
        $this->_init(\Hexamarvel\FlexibleForm\Model\ResourceModel\FieldSet::class);
    }
}
