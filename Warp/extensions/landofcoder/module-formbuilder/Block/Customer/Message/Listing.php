<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_Formbuilder
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\Formbuilder\Block\Customer\Message;

use Lof\Formbuilder\Model\Form;
use Lof\Formbuilder\Model\FormFactory;
use Lof\Formbuilder\Model\Message;
use Lof\Formbuilder\Model\Message\Collection;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Theme\Block\Html\Pager;

class Listing extends Template
{
    /**
     * @var Message
     */
    protected $message;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var SessionFactory
     */
    protected $customerSession;

    /**
     * @var Collection|null
     */
    protected $messageCollection = null;

    /**
     * @var Customer|null
     */
    protected $customer = null;

    /**
     * Listing constructor.
     * @param Context $context
     * @param Message $message
     * @param FormFactory $formFactory
     * @param SessionFactory $customerSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Message $message,
        FormFactory $formFactory,
        SessionFactory $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->message        = $message;
        $this->formFactory    = $formFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * @return Customer|null
     */
    public function getCustomer()
    {
        if (!$this->customer) {
            $this->customer = $this->customerSession->create()->getCustomer();
        }
        return $this->customer;
    }

    /**
     * @inheritdoc
     */
    protected function _beforeToHtml()
    {
        $collection = $this->getCollection();
        $item_per_page = 5;
        $pager = $this->getLayout()->createBlock(Pager::class, 'my.formmessage.pager');
        if ($pager && $collection) {
            $pager->setLimit($item_per_page)
                    ->setCollection($collection);
            $this->setChild('pager', $pager);
        }

        return parent::_beforeToHtml();
    }

    /**
     * @inheritdoc
     */
    public function _toHtml()
    {
        if (!$this->getCustomer()) {
            return "";
        }
        $template = 'Lof_Formbuilder::customer/message/list.phtml';
        if ($blockTemplate = $this->getConfig('block_template')) {
            $template = $blockTemplate;
        }
        $this->setTemplate($template);

        return parent::_toHtml();
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param Collection|mixed $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->messageCollection = $collection;
        return $this;
    }

    /**
     * @return Collection|null
     */
    public function getCollection()
    {
        if (!$this->messageCollection) {
            $customer = $this->getCustomer();
            $collection = $this->message->getCollection();
            if ($customer && $customer->getId()) {
                $collection
                    ->addFieldToFilter('customer_id', (int)$customer->getId())
                    ->setOrder("message_id", "DESC");

            } else {
                $collection
                    ->addFieldToFilter('customer_id', -1)
                    ->setOrder("message_id", "DESC");
            }
            $this->setCollection($collection);
        }
        return $this->messageCollection;
    }

    /**
     * @param $key
     * @param string $default
     * @return array|mixed|string|null
     */
    public function getConfig($key, string $default = '')
    {
        if ($this->hasData($key)) {
            return $this->getData($key);
        }

        return $default;
    }
}
