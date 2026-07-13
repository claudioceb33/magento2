<?php

namespace Swissup\SeoImages\Block\Adminhtml\Config\Form;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\App\ActionInterface;

class FlushCache extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * @param \Magento\Framework\Url\Helper\Data      $urlHelper
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->urlHelper = $urlHelper;
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
        $button = $this->getLayout()
            ->createBlock('Magento\Backend\Block\Widget\Button')
            ->setLabel(__('Clean cached names'))
            ->setId('seoimages_flush')
            ->setDataAttribute([
                'post' => [
                    'action' => $this->getUrl('seoimages/index/flush'),
                    'data' => [
                        ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlHelper->getEncodedUrl(),
                        'confirmation' => true,
                        'confirmationMessage' => __('<p>Think twice before cleaning SEO names cache. If module is in production mode then you\'ll have to disable production or run `bin/magento catalog:images:resize`.</p><p>Do you want to clean cache still?</p>')
                    ]
                ]
            ]);

        return $button->toHtml();
    }

    /**
     * {@inheritdoc}
     */
    protected function _renderScopeLabel(AbstractElement $element)
    {
        return '';
    }
}
