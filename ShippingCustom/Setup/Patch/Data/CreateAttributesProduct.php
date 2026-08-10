<?php
namespace Ceb\ShippingCustom\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Catalog\Model\Config;

class CreateAttributesProduct implements DataPatchInterface
{
    const ATTRIBUTE_GROUP = 'General';

    /** @var ModuleDataSetupInterface */
    protected $setup;

    /** @var EavSetupFactory  */
    protected $eavSetupFactory;

    /**
     * @var AttributeManagementInterface 
     */
    protected $attributeManagement;

    /**
     * @var Config
     */
    protected $config;

    public function __construct(
        ModuleDataSetupInterface $setup,
        EavSetupFactory $eavSetupFactory,
        AttributeManagementInterface $attributeManagement,
        Config $config
    ) {
        $this->setup = $setup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeManagement = $attributeManagement;
        $this->config = $config;
    }

    public function apply()
    {
    
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup]);
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeGroup = self::ATTRIBUTE_GROUP;

        $attributesInfo = [
            "free_shipping_city" => [
                'label' => 'Free Shipping City',
                'type' => 'int',
                'input' => 'boolean',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'attribute_set' => 'Default',
                'visible' => false,
                'filterable' => false,
                'required' => false,
                'option' => ''
            ]
        ];

        foreach ($attributesInfo as $key => $attribute){
            $attributeSet = $attribute['attribute_set'];
            if(is_array($attributeSet)) {
                foreach ($attributeSet as $productAttribute) {
                    $AttributeSet = $productAttribute;
                    $this->createAttribute($eavSetup, $key, $attributeSet, $attribute);
                }
            } else {
                $this->createAttribute($eavSetup, $key, $attributeSet, $attribute);
            }
            $attributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);
            foreach ($attributeSetIds as $attributeSetId) {
                if ($attributeSetId) {
                    $groupId = $this->config->getAttributeGroupId($attributeSetId, $attributeGroup);
                    $this->attributeManagement->assign(
                        'catalog_product',
                        $attributeSetId,
                        $groupId,
                        $key,
                        999
                    );
                }
            }
        }
    }

    public function createAttribute($eavSetup, $key, $attributeSet, $attribute)
    {
        $eavSetup->addAttribute(
            Product::ENTITY,
            $key,
            [
                'attribute_set' => $attributeSet,
                'type' => $attribute['type'],
                'backend' => isset($attribute['option'])?'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend':'',
                'label' => $attribute['label'],
                'input' => $attribute['input'],
                'option' => $attribute['option'],
                'required' => $attribute['required'],
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'user_defined' => true,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'used_in_product_listing' => true,
                'is_visible_in_advanced_search' => false,
                'filterable' => $attribute['filterable'],
                'visible_on_front' => $attribute['visible'],
                'source' => (isset($attribute['source'])) ? $attribute['source'] : ''
            ]
        );
        $eavSetup->addAttributeToSet(Product::ENTITY, $attributeSet, 'General', $key);
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies() {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases() {
        return [];
    }
}