<?php
namespace Lof\Formbuilder\Block\Adminhtml\Form\Renderer;
use Magento\Framework\UrlInterface;

class MessageAction extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
	/**
	 * @var Magento\Framework\UrlInterface
	 */
	protected $_urlBuilder;

	/**
	 * @param \Magento\Backend\Block\Context
	 * @param UrlInterface
	 */
	public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Url $urlBuilder
    ) {
		$this->_urlBuilder = $urlBuilder;
        parent::__construct($context);
	}

    /**
     * get value
     *
     * @param \Magento\Framework\DataObject $row
     * @return string|mixed
     */
	public function _getValue(\Magento\Framework\DataObject $row)
    {
		$editUrl = $this->_urlBuilder->getUrl('formbuilder/message/edit', ['message_id' => $row['message_id']]);
		return __("<a target='_blank' href='%1'>Edit</a>", $editUrl);
	}
}
