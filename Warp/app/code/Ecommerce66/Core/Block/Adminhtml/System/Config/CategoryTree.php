<?php
namespace Ecommerce66\Core\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Store\Model\StoreManagerInterface;

class CategoryTree extends Field
{
    /**
     * Path to template that renders the tree and JS
     * @var string
     */
    protected $_template = 'Ecommerce66_Core::system/config/category-tree.phtml';

    /** @var CategoryFactory */
    protected $categoryFactory;
    /** @var StoreManagerInterface */
    protected $storeManager;

    public function __construct(
        CategoryFactory $categoryFactory,
        StoreManagerInterface $storeManager,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->storeManager    = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Render HTML for the field via template
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        // Store element for template
        $this->setElement($element);
        return $this->_toHtml();
    }

    /**
     * Retrieve the configuration field name
     *
     * @return string
     */
    public function getFieldName()
    {
        return $this->getElement()->getName();
    }

    /**
     * Retrieve checkbox input name (array)
     *
     * @return string
     */
    public function getCheckboxName()
    {
        return $this->getFieldName() . '[]';
    }

    /**
     * Retrieve selected values array
     *
     * @return array
     */
    public function getSelected()
    {
        $value = $this->getElement()->getValue();
        return is_array($value) ? $value : [];
    }

    /**
     * Retrieve root category ID for current store
     *
     * @return int
     */
    public function getRootId()
    {
        return (int)$this->storeManager->getStore()->getRootCategoryId();
    }


    /**
     * Recursively render categories as nested checkboxes with toggler
     *
     * @param int    $parentId
     * @param string $fieldName
     * @param array  $selected
     * @param mixed  $rootLevel
     * @return string
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function renderTree($parentId, $fieldName, array $selected, $rootLevel = false)
    {
        $collection = $this->categoryFactory->create()->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToFilter('parent_id', $parentId)
            ->setOrder('position', 'ASC');

        if (!$collection->getSize()) {
            return '';
        }

        // Collapse non-root levels
        $ulClass = $rootLevel ? '' : 'class="child"';
        $html = "<ul {$ulClass}>";
        foreach ($collection as $category) {
            $id      = (int)$category->getId();
            $name    = $this->escapeHtml($category->getName());
            $checked = in_array($id, $selected) ? 'checked' : '';

            // Check if this category has children
            $hasChildren = (bool)$this->categoryFactory->create()->getCollection()
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToFilter('parent_id', $id)
            ->getSize();

            $html .= '<li style="margin-bottom:5px; position:relative;">';
            $node = '<span class="node"></span>';
            if ($hasChildren) {
                $node = '<a href="#" class="node category-toggle">+</a>';
            }
            $html .= $node.'<label style="font-weight:normal;">';
            $html .= '<input type="checkbox" name="' . $fieldName . '" value="' . $id . '" ' . $checked . ' /> ' . $name;
            $html .= '</label>';

            // Render children (collapsed by default)
            $html .= $this->renderTree($id, $fieldName, $selected, false);
            $html .= '</li>';
        }
        $html .= '</ul>';

        return $html;
    }
}
