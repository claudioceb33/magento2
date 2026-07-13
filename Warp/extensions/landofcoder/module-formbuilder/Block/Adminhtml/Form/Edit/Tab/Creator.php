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

namespace Lof\Formbuilder\Block\Adminhtml\Form\Edit\Tab;

use Lof\Formbuilder\Model\Modelcategory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;

class Creator extends Generic implements
    TabInterface
{
    /**
     * @var Config
     */
    protected $wysiwygConfig;
    protected $modelCategory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Config $wysiwygConfig
     * @param Modelcategory $modelCategory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        Modelcategory $modelCategory,
        array $data = []
    ) {
        $this->wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->setTemplate('Lof_Formbuilder::edit/editor.phtml');
        $this->modelCategory = $modelCategory;
    }

    /**
     * @return mixed|null
     */
    public function getCurrentForm()
    {
        return $this->_coreRegistry->registry('formbuilder_form');
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('formbuilder_form');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('form_');
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Form Creator');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Form Creator');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return bool
     */
    public function canShowTab(): bool
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
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
     * @param $haystack
     * @param $needle
     * @return bool
     */
    public function startsWith($haystack, $needle): bool
    {
        if ($haystack && @strlen($haystack) >= 1) {
            return $needle === "" || @strrpos($haystack, $needle, -@strlen($haystack)) !== false;
        }
        return false;
    }

    /**
     * @param $haystack
     * @param $needle
     * @return bool
     */
    public function endsWith($haystack, $needle): bool
    {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = @strlen($haystack) - @strlen($needle)) >= 0 && @strpos(
            $haystack,
            $needle,
            $temp
        ) !== false);
    }

    /**
     * @return false|string
     */
    public function getModelCategories(): bool|string
    {
        $json = [];
        $collection = $this->modelCategory->getCollection();
        if (0 < $collection->getSize()) {
            foreach ($collection as $item) {
                $tmp = [];
                $tmp['value'] = $item->getId();
                $tmp['label'] = $item->getTitle();
                $json[] = $tmp;
            }
        }
        return @json_encode($json);
    }
}
