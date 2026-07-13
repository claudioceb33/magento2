<?php

namespace Swissup\SeoTemplates\Model;

use Magento\CatalogRule\Api\Data\RuleInterface;

class Template extends \Magento\Rule\Model\AbstractModel
{
    /**
     * Template's statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * Template's entity types
     */
    const ENTITY_TYPE_PRODUCT = 1001;
    const ENTITY_TYPE_CATEGORY = 1002;

    /**
     * Template's data types
     */
    const DATA_META_TITLE = 2001;
    const DATA_META_DESCRIPTION = 2002;
    const DATA_META_KEYWORDS = 2003;
    const DATA_IMAGE_ALT = 2004;
    const DATA_H1_TAG = 2005;

    protected $combineFactory;

    protected $actionCollectionFactory;

    protected $processor;

    private $memoizedUnderscore = [];

    /**
     * @return void
     */
    protected function _construct()
    {
        // combineFactory and actionCollectionFactory passed to __construct via di.xml
        // they have to be saved into protected properties because load() overrides _data
        $this->combineFactory = $this->getData('combineFactory');
        $this->actionCollectionFactory = $this->getData('actionCollectionFactory');
        $this->processor = $this->getData('processor');
        $this->unsetData('combineFactory');
        $this->unsetData('actionCollectionFactory');
        $this->unsetData('processor');
        $this->_init('Swissup\SeoTemplates\Model\ResourceModel\Template');
    }

    /**
     * Convert sname to code
     *
     * @param  string $name
     * @return string
     */
    protected function _nameToCode($name)
    {
        $name = ucwords($name); // make sure every work starts with Upper case
        $name = str_replace(' ', '', $name); // remove spaces
        return str_replace(  // fix for `h1_tag`
            'h_1_',
            'h1_',
            $this->convertToUnderscore($name)
        );
    }

    private function convertToUnderscore(string $str): string
    {
        if (!isset($this->memoizedUnderscore[$str])) {
            $this->memoizedUnderscore[$str] = strtolower(trim(
                preg_replace('/([A-Z]|[0-9]+)/', "_$1", $str),
                '_')
            );
        }

        return $this->memoizedUnderscore[$str];
    }

    /**
     * Prepare array of available statuses for template
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled')
        ];
    }

    /**
     * Prepare array of available entity types for template
     *
     * @return array
     */
    public function getAvailableEntityTypes($translateLabel = true)
    {
        $types = [
            self::ENTITY_TYPE_PRODUCT => 'Catalog Product',
            self::ENTITY_TYPE_CATEGORY => 'Catalog Category'
        ];
        return $translateLabel ? array_map('__', $types) : $types;
    }

    /**
     * Prepare array of available data types for template
     *
     * @return array
     */
    public function getAvailableDataNames($translateLabel = true)
    {
        $dataTypes = [
            self::DATA_META_TITLE => 'Meta Title',
            self::DATA_META_DESCRIPTION => 'Meta Description',
            self::DATA_META_KEYWORDS => 'Meta Keywords',
            self::DATA_IMAGE_ALT => 'Image Alt',
            self::DATA_H1_TAG => 'H1 Tag'
        ];
        return $translateLabel ? array_map('__', $dataTypes) : $dataTypes;
    }

    /**
     * Getter for rule conditions collection
     *
     * @return
     */
    public function getConditionsInstance()
    {
        return $this->combineFactory->create();;
    }

    /**
     * Getter for rule actions collection
     *
     * @return
     */
    public function getActionsInstance()
    {
        return $this->actionCollectionFactory->create();
    }

    /**
     * @param string $formName
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    /**
     * Get processor base on template entity type
     *
     * @return mixed
     */
    protected function _getProcessor()
    {
        $processorName = $this->getEntityTypeCode($this->getEntityType());
        return isset($this->processor[$processorName])
            ? $this->processor[$processorName]
            : null;
    }

    /**
     * Get entity type name
     *
     * @param  int $entityType
     * @return mixed
     */
    public function getEntityTypeName($entityType, $translateLabel = true)
    {
        $types = $this->getAvailableEntityTypes($translateLabel);
        return isset($types[$entityType]) ? $types[$entityType] : null;
    }

    /**
     * Get seodata type name
     *
     * @param  int $seodataType
     * @return mixed
     */
    public function getDataNameName($seodataName, $translateLabel = true)
    {
        $types = $this->getAvailableDataNames($translateLabel);
        return isset($types[$seodataName]) ? $types[$seodataName] : null;
    }

    /**
     * Get string code for entity type
     *
     * @param  int $entityType
     * @return mixed
     */
    public function getEntityTypeCode($entityType)
    {
        return $this->_nameToCode($this->getEntityTypeName($entityType, false));
    }

    /**
     * Get string code for seodata type
     *
     * @param  int $seodataType
     * @return mixed
     */
    public function getDataNameCode($seodataName)
    {
        return $this->_nameToCode($this->getDataNameName($seodataName, false));
    }

    /**
     * Generate string from template using $entity
     *
     * @param  \Magento\Framework\Model\AbstractModel $entity
     * @return string
     */
    public function generate($entity)
    {
        $processor = $this->_getProcessor()->setScope($entity);
        return $processor->filter($this->getTemplate());
    }

    /**
     * Clear log records for current template
     *
     * @param  array $entityIds
     * @return $this
     */
    public function clearLog($entityIds = [])
    {
        $this->getResource()->clearLog($this->getId(), $entityIds);
        return $this;
    }

    public function getStorefrontConditions()
    {
        if (!$this->hasData('storefront_conditions')) {
            $combineCondition = $this->getConditions();
            $this->setData(
                'storefront_conditions',
                $this->prepareStorefrontCondition($combineCondition)
            );
        }

        return $this->getData('storefront_conditions');
    }

    private function prepareStorefrontCondition($condition)
    {
        $storefrontTypes = [
            Rule\Condition\Category::class => [
                'attribute' => [
                    'applied_filters'
                ],
                'storefront_type' => Rule\StorefrontCondition\Category::class
            ]
        ];
        $conditionClass = get_class($condition);
        $isCombine = ($conditionClass == Rule\Condition\Combine::class);
        if ($isCombine) {
            $conditions = array_map(
                [$this, 'prepareStorefrontCondition'],
                $condition->getConditions()
            );
            $conditions = array_filter($conditions);

            return $conditions ?
                [
                    'type' => Rule\StorefrontCondition\Combine::class,
                    'aggregator' => $condition->getAggregator(),
                    'value' => $condition->getValue(),
                    'conditions' => $conditions
                ] :
                [];
        }

        $storefrontType = $storefrontTypes[$conditionClass] ?? null;
        $storefronAttributes = $storefrontType['attribute'] ?? [];
        if (in_array($condition->getAttribute(), $storefronAttributes)) {
            return [
                'type' => $storefrontType['storefront_type'],
                'attribute' => $condition->getAttribute(),
                'operator' => $condition->getOperator(),
                'value' => $condition->getValue()
            ];
        }

        return [];
    }
}
