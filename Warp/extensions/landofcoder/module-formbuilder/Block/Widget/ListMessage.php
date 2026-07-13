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

namespace Lof\Formbuilder\Block\Widget;

use Lof\Formbuilder\Model\Message;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Theme\Block\Html\Pager;
use Magento\Widget\Block\BlockInterface;

class ListMessage extends Template implements BlockInterface
{
    /**
     * @var Message
     */
    protected $message;

    /**
     * @var AbstractCollection
     */
    protected $postCollection;

    /**
     * ListMessage constructor.
     * @param Context $context
     * @param Message $message
     * @param array $data
     */
    public function __construct(
        Context $context,
        Message $message,
        array $data = []
    ) {
        $this->message = $message;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    public function _toHtml()
    {
        $gridPagination = $this->getConfig('grid_pagination');
        $formid = $this->getConfig('formid');
        $template = 'Lof_Formbuilder::widget/list.phtml';
        if ($blockTemplate = $this->getConfig('block_template')) {
            $template = $blockTemplate;
        }
        $this->setTemplate($template);
        $item_per_page = (int)$this->getConfig('item_per_page');
        //$store = $this->storeManager->getStore();
        $collection = $this->message->getCollection();
        $collection->getSelect()->where('main_table.form_id =' . $formid)->order('message_id DESC');
        if ($gridPagination) {
            $pager = $this->getLayout()->createBlock(Pager::class, 'my.custom.pager');
            $pager->setLimit($item_per_page)->setCollection($collection);
            $this->setChild('pager', $pager);
        }
        $this->setCollection($collection);
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
     * @param AbstractCollection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->postCollection = $collection;
        return $this;
    }

    /**
     * Get collection
     * @return AbstractCollection
     */
    public function getCollection()
    {
        return $this->postCollection;
    }

    /**
     * Get config
     *
     * @param string $key
     * @param mixed|string $default
     * @return string|mixed
     */
    public function getConfig(string $key, null|string $default = '')
    {
        if ($this->hasData($key)) {
            return $this->getData($key);
        }
        return $default;
    }
}
