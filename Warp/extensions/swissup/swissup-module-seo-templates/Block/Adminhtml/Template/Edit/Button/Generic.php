<?php

namespace Swissup\SeoTemplates\Block\Adminhtml\Template\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Generic implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * Generic constructor
     *
     * @param \Magento\Framework\View\Element\UiComponent\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\Context $context
    ) {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [];
    }
}
