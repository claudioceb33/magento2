<?php
/**
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

namespace Lof\Formbuilder\Block\Adminhtml\Modelcategory\Edit\Tab;

use Lof\Formbuilder\Model\ResourceModel\Modelcategory\Collection;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject as ObjectConverter;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;

class Main extends Generic implements TabInterface
{
    /**
     * @var Store
     */
    protected $systemStore;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ObjectConverter
     */
    protected $objectConverter;

    /**
     * @var Collection
     */
    protected $categoryCollection;

    /**
     * @var CollectionFactory
     */
    protected $templatesFactory;
    /**
     * @var Config
     */
    protected $emailConfig;

    /**
     * @var array
     */
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
     * @param Collection $categoryCollection
     * @param Config $emailConfig
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
        Collection $categoryCollection,
        Config $emailConfig,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->groupRepository = $groupRepository;
        $this->objectConverter = $objectConverter;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->templatesFactory = $templatesFactory;
        $this->emailConfig = $emailConfig;
        $this->categoryCollection = $categoryCollection;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('formbuilder_modelcategory');

        if ($this->isAllowedAction('Lof_Formbuilder::category_edit')) {
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
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Category Information')]);
        if ($model->getCategoryId()) {
            $fieldset->addField('category_id', 'hidden', ['name' => 'category_id']);
        }
        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $categories[] = ['label' => __('Please select'), 'value' => 0];
        $this->drawLevel = $categories;
        $collection = $this->getCatCollection();
        $cats = [];
        foreach ($collection as $_cat) {
            if (!$_cat->getParentId()) {
                $cat = [
                    'label' => $_cat->getTitle(),
                    'value' => $_cat->getId(),
                    'id' => $_cat->getId(),
                    'parent_id' => $_cat->getParentId(),
                    'level' => 0
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
     * @param string $n
     * @return string
     */
    protected function getSpaces($n): string
    {
        return str_repeat('--- ', $n);
    }

    /**
     * @param $collection
     * @param $cat
     * @param int $level
     * @return mixed
     */
    public function drawItems($collection, $cat, int $level = 0)
    {
        foreach ($collection as $_cat) {
            if ($_cat->getParentId() == $cat['id']) {
                $cat1 = [
                    'label' => $_cat->getTitle(),
                    'value' => $_cat->getId(),
                    'id' => $_cat->getId(),
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
     * @return Collection
     */
    public function getCatCollection()
    {
        $model = $this->_coreRegistry->registry('formbuilder_modelcategory');
        return $this->categoryCollection
            ->addFieldToFilter('category_id', ['neq' => $model->getCategoryId()]);
    }

    /**
     * Prepare label for tab
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Category Information');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Category Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden(): bool
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function isAllowedAction(string $resourceId): bool
    {
        return $this->_authorization->isAllowed($resourceId);
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
}
