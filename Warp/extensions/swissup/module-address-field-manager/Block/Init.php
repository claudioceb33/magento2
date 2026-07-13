<?php

namespace Swissup\AddressFieldManager\Block;

use Magento\Framework\View\Element\Template;

class Init extends Template
{
    protected $_template = 'Swissup_AddressFieldManager::init.phtml';

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @param Template\Context                         $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array                                    $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * @return string
     */
    public function getJsonConfig()
    {
        return $this->jsonEncoder->encode([
            'mutable' => $this->getMutable(),
            'selectors' => $this->getSelectors()
        ]);
    }
}
