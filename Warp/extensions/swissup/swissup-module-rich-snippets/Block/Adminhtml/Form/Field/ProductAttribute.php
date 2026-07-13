<?php

namespace Swissup\RichSnippets\Block\Adminhtml\Form\Field;

class ProductAttribute extends \Magento\Framework\View\Element\Html\Select
{
    private $productAttributes;
    /**
     * Customer groups cache
     *
     * @var array
     */
    private $_customerGroups;

    /**
     * Flag whether to add group all option or no
     *
     * @var bool
     */
    protected $_addGroupAllOption = true;

    /**
     * @var GroupManagementInterface
     */
    protected $groupManagement;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param GroupManagementInterface $groupManagement
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Swissup\RichSnippets\Model\Config\Source\ProductAttributes $productAttributes,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->productAttributes = $productAttributes;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->productAttributes->toOptionArray() as $option) {
                $this->addOption($option['value'], addslashes($option['label']));
            }
        }

        return parent::_toHtml();
    }
}
