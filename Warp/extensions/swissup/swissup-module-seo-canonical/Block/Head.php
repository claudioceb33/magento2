<?php

namespace Swissup\SeoCanonical\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Api\SimpleDataObjectConverter as Converter;

class Head extends Template
{
    /**
     * @var \Swissup\SeoCanonical\Helper\Data
     */
    protected $helper;

    /**
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Swissup\SeoCanonical\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $camelizedEntityType = Converter::snakeCaseToUpperCamelCase(
            $this->getEntityType()
        );
        $methodName = "get{$camelizedEntityType}CanonicalUrl";
        $callback = [$this->helper, $methodName];
        if (is_callable($callback)) {
            $url = call_user_func($callback);
            if ($url) {
                $this->pageConfig->addRemotePageAsset(
                    $url,
                    'canonical',
                    [
                        'attributes' => [
                            'rel' => 'canonical'
                        ]
                    ]
                );
            }
        }

        return parent::_prepareLayout();
    }
}
