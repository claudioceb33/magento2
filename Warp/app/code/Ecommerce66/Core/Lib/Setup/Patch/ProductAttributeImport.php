<?php
declare(strict_types=1);

namespace Ecommerce66\Core\Lib\Setup\Patch;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Ecommerce66\Core\Helper\CsvRead;

class ProductAttributeImport implements DataPatchInterface, PatchRevertableInterface
{
    protected $moduleName = 'Ecommerce66_Core';
    protected $fileName  = 'Setup/file/product_attributes.csv';
    protected $delimiter = ';';
    protected $groupAttr = 'General';

    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var CsvRead
     */
    protected $csvHelper;

    /**
     * @var string
     */
    protected $productTypes;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        CsvRead $csvHelper
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->csvHelper = $csvHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $csvData = $this->csvHelper->readCsv($this->fileName, $this->delimiter, $this->moduleName);
        //var_dump($csvData[0]);die;

        $order = 100;
        foreach ($csvData as $itemData) {
            //var_dump($itemData);die;
            $order++;
            $input = isset($itemData['input']) ? $itemData['input'] : 'default';
            $models = $this->getModelsByInput($input);
            if (empty($models) || !isset($itemData['label']) || !isset($itemData['code'])) {
                continue;
            }
            $attributeData = [
                'type'                    => $models['type'],
                'label'                   => $itemData['label'],
                'input'                   => $itemData['input'],
                'source'                  => $models['source_model'],
                'frontend'                => $models['frontend_model'],
                'frontend_class'          => $models['frontend_class'],
                'backend'                 => $models['backend_model'],
                'table'                   => $models['backend_table'],
                'attribute_model'         => $models['attribute_model'],
                'required'                => (bool)(int)$itemData['required'],
                'sort_order'              => isset($itemData['sort_order']) ? $itemData['sort_order'] : $order,
                'global'                  => ScopedAttributeInterface::SCOPE_GLOBAL,
                'default'                 => null,
                'visible'                 => true,
                'user_defined'            => true,
                'searchable'              => true,
                'filterable'              => (bool)(int)$itemData['is_filterable'],
                'comparable'              => false,
                'visible_on_front'        => (bool)(int)$itemData['is_visible_on_front'],
                'unique'                  => false,
                'apply_to'                => $this->getProductTypes(),
                'group'                   => $this->groupAttr,
                'used_in_product_listing' => true,
                'is_used_in_grid'         => (bool)(int)$itemData['is_used_in_grid'],
                'is_visible_in_grid'      => (bool)(int)$itemData['is_filterable_in_grid'],
                'is_filterable_in_grid'   => (bool)(int)$itemData['is_filterable_in_grid'],
            ];

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $itemData['code'],
                $attributeData
            );
        }
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return string
     */
    protected function getProductTypes()
    {
        if (empty($this->productTypes)) {
            $productTypes = [
                \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
                \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
                \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE,
                \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE,
            ];
            $this->productTypes = join(',', $productTypes);
        }

        return $this->productTypes;
    }

    /**
     * @param $input
     *
     * @return array
     */
    protected function getModelsByInput($input)
    {
        $models = [];
        switch ($input) {
            case 'text':
                $models = [
                    'type' => 'varchar',
                    'attribute_model' => null,
                    'backend_model' => null,
                    'backend_table' => null,
                    'frontend_model' => null,
                    'frontend_class' => null,
                    'source_model' => null,
                ];
                break;
            //case 'textarea':
            //case 'texteditor':
            //case 'pagebuilder':
            //case 'date':
            //case 'datetime':
            case 'boolean':
                $models = [
                    'type' => 'int',
                    'attribute_model' => null,
                    'backend_model' => null,
                    'backend_table' => null,
                    'frontend_model' => null,
                    'frontend_class' => null,
                    'source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                ];
                break;
            case 'select':
                $models = [
                    'type' => 'int',
                    'attribute_model' => null,
                    'backend_model' => null,
                    'backend_table' => null,
                    'frontend_model' => null,
                    'frontend_class' => null,
                    'source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                ];
                break;
            case 'multiselect':
                $models = [
                    'type' => 'text',
                    'attribute_model' => null,
                    'backend_model' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
                    'backend_table' => null,
                    'frontend_model' => null,
                    'frontend_class' => null,
                    'source_model' => null,
                ];
                break;
            //case 'price':
            //case 'media_image':
            //case 'swatch_visual':
            //case 'swatch_text':
            //case 'weee':
        }

        return $models;
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'brand');

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }
}
