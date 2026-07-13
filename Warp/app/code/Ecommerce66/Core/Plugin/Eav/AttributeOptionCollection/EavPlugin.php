<?php
namespace Ecommerce66\Core\Plugin\Eav\AttributeOptionCollection;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection as EavAttributeOptionCollection;

class EavPlugin
{
    /**
     * @param EavAttributeOptionCollection $subject
     * @param                              $result
     *
     * @return mixed
     */
    public function beforeSetPositionOrder(EavAttributeOptionCollection $subject, $result)
    {
        //$sortAlpha = true;

        return $result;
    }
}
