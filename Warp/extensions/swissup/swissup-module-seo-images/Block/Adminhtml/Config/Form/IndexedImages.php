<?php

namespace Swissup\SeoImages\Block\Adminhtml\Config\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Swissup\SeoImages\Model\ResourceModel\Index as IndexResource;

class IndexedImages extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var IndexResource
     */
    protected $indexResource;

    /**
     * @param IndexResource $indexResource
     * @param Context       $context
     * @param array         $data
     */
    public function __construct(
        IndexResource $indexResource,
        Context $context,
        array $data = []
    ) {
        $this->indexResource = $indexResource;
        parent::__construct($context, $data);
    }

    /**
     * Render element HTML
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setText($this->indexResource->countImages());

        return str_replace('admin__field-value', '', parent::_getElementHtml($element));
    }

    /**
     * {@inheritdoc}
     */
    protected function _renderScopeLabel(AbstractElement $element)
    {
        return '';
    }
}
