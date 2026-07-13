<?php

namespace Swissup\SeoImages\Block\Adminhtml\Config\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Swissup\SeoImages\Model\ResourceModel\Entity as ImageResource;

class CachedNames extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var ImageResource
     */
    protected $imageResource;

    /**
     * @param ImageResource $imageResource
     * @param Context       $context
     * @param array         $data
     */
    public function __construct(
        ImageResource $imageResource,
        Context $context,
        array $data = []
    ) {
        $this->imageResource = $imageResource;
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
        $element->setText($this->imageResource->countUniquePairs());

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
