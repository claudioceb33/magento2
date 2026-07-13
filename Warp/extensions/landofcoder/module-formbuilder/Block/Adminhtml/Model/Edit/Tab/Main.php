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

namespace Lof\Formbuilder\Block\Adminhtml\Model\Edit\Tab;

use Lof\Formbuilder\Model\Model;
use Lof\Formbuilder\Model\Modelcategory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject as ObjectConverter;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;

class Main extends Generic implements TabInterface
{
    /**
     * @var Store
     */
    protected Store $systemStore;

    /**
     * @var GroupRepositoryInterface
     */
    protected GroupRepositoryInterface $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var ObjectConverter
     */
    protected ObjectConverter $objectConverter;

    /**
     * @var Modelcategory
     */
    protected Modelcategory $category;
    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $templatesFactory;
    /**
     * @var Config
     */
    protected Config $emailConfig;
    protected array $drawLevel = [];

    /**
     * Main constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param GroupRepositoryInterface $groupRepository
     * @param ObjectConverter $objectConverter
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Store $systemStore
     * @param CollectionFactory $templatesFactory
     * @param Config $emailConfig
     * @param Model $model
     * @param Modelcategory $category
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        GroupRepositoryInterface $groupRepository,
        ObjectConverter $objectConverter,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Store $systemStore,
        CollectionFactory $templatesFactory,
        Config $emailConfig,
        Model $model,
        Modelcategory $category,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->groupRepository = $groupRepository;
        $this->objectConverter = $objectConverter;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->templatesFactory = $templatesFactory;
        $this->emailConfig = $emailConfig;
        $this->_model = $model;
        $this->category = $category;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Phrase|string
     */
    public function getTabLabel(): Phrase|string
    {
        return __('Model Information');
    }

    /**
     * @return Phrase
     */
    public function getTabTitle(): Phrase
    {
        return __('Model Information');
    }

    /**
     * @return bool
     */
    public function canShowTab(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden(): bool
    {
        return false;
    }

    protected function _prepareForm(): Main
    {
        $model = $this->_coreRegistry->registry('formbuilder_model');

        if ($this->isAllowedAction('Lof_Formbuilder::model_edit')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }
        $this->_eventManager->dispatch(
            'lof_check_license',
            ['obj' => $this, 'ex' => 'Lof_Formbuilder']
        );
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('form_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Model Information')]);
        if ($model->getId()) {
            $fieldset->addField('model_id', 'hidden', ['name' => 'model_id']);
        }
        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Model Title'),
                'title' => __('Model Title'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $categories[] = ['label' => __('-- Select a Parent --'), 'value' => 0];
        $this->drawLevel = $categories;
        $collection = $this->getModelCollection();
        $cats = [];
        foreach ($collection as $model) {
            if (!$model->getParentId()) {
                $label = $model->getTitle();
                if ($model->getCategoryId()) {
                    $label = $label . '(' . ' Cat: ' . $this->loadNameCategory($model->getCategoryId()) . ')';
                }
                $cat = [
                    'label' => $label,
                    'value' => $model->getId(),
                    'id' => $model->getId()
                ];
                $cats[] = $this->drawItems($collection, $cat);
            }
        }
        $this->drawSpaces($cats);

        if (count($this->drawLevel)) {
            $fieldset->addField(
                'parent_id',
                'select',
                [
                    'name' => 'parent_id',
                    'label' => __('Parent'),
                    'title' => __('Parent'),
                    'values' => $this->drawLevel,
                    'disabled' => $isElementDisabled
                ]
            );
        }
        $category = ['label' => __('-- Select a Category --'), 'value' => 0];
        $cats = $this->category->getCollection()->toOptionArray();
        array_unshift($cats, $category);

        if (count($this->drawLevel)) {
            $fieldset->addField(
                'category_id',
                'select',
                [
                    'name' => 'category_id',
                    'label' => __('Category'),
                    'title' => __('Category'),
                    'values' => $cats,
                    'disabled' => $isElementDisabled
                ]
            );
        }

        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'options' => $model->getAvailableStatuses(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'position',
            'text',
            [
                'name' => 'position',
                'label' => __('Position'),
                'title' => __('Position'),
                'disabled' => $isElementDisabled
            ]
        );
        if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '0' : '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param $resourceId
     * @return bool
     */
    protected function isAllowedAction($resourceId): bool
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * @return AbstractDb|AbstractCollection|null
     */
    public function getModelCollection(): AbstractDb|AbstractCollection|null
    {
        $model = $this->_coreRegistry->registry('formbuilder_model');
        return $this->_model->getCollection()
            ->addFieldToFilter('model_id', ['neq' => $model->getModelId()]);
    }

    /**
     * @param $id
     */
    public function loadNameCategory($id)
    {
        $this->category->load($id)->getTitle();
    }

    /**
     * @param $collection
     * @param $cat
     * @param int $level
     * @return mixed
     */
    public function drawItems($collection, $cat, int $level = 0): mixed
    {
        foreach ($collection as $_cat) {
            if ($_cat->getParentId() == $cat['id']) {
                $cat1 = [
                    'label' => $_cat->getTitle(),
                    'value' => $_cat->getModelId(),
                    'id' => $_cat->getModelId(),
                    'parent_id' => $_cat->getParentId(),
                    'level' => 0,
                    'postion' => $_cat->getCatPosition()
                ];
                $children[] = $this->drawItems($collection, $cat1, $level + 1);
                $cat['children'] = $children;
            }
        }
        $cat['level'] = $level;
        return $cat;
    }

    /**
     * @param $cats
     */
    public function drawSpaces($cats)
    {
        if (is_array($cats)) {
            foreach ($cats as $v) {
                $v['label'] = $this->getSpaces($v['level']) . $v['label'];
                $this->drawLevel[] = $v;
                if (isset($v['children']) && $children = $v['children']) {
                    $this->drawSpaces($children);
                }
            }
        }
    }

    /**
     * @param $n
     * @return string
     */
    protected function getSpaces($n): string
    {
        return str_repeat('--- ', $n);
    }
}
