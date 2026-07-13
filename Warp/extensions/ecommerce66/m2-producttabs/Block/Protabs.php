<?php

namespace Ecommerce66\ProductTabs\Block;

use Magento\Framework\View\Element\Template;

/**
 * Main contact form block
 */
class Protabs extends Template
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Protabs constructor.
     *
     * @param Template\Context                          $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array                                     $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_objectManager = $objectManager;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->_objectManager->create('Ecommerce66\ProductTabs\Model\Protabs');
    }
}
