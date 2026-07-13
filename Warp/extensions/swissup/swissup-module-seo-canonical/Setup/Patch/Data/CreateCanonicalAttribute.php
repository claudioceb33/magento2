<?php

namespace Swissup\SeoCanonical\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreateCanonicalAttribute implements DataPatchInterface
{
    const ATTRIBUTE_NAME = 'swissup_seocanonical_link';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    public function apply()
    {
        $this->categorySetupFactory
            ->create(['setup' => $this->moduleDataSetup])
            ->addAttribute(Category::ENTITY, 'swissup_seocanonical_link', [
                'type' => 'varchar',
                'label' => 'Canonical link',
                'input' => 'text',
                'sort_order' => 100,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'default' => null,
                'group' => 'SEO Suite'
            ]);

        return $this;
    }

    public static function getVersion()
    {
        return '1.0.2';
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
