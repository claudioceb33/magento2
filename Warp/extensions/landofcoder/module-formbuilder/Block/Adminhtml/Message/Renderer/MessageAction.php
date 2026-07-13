<?php /**
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

namespace Lof\Formbuilder\Block\Adminhtml\Message\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\Text;
use Magento\Framework\DataObject;
use Magento\Framework\Url;
use Magento\Framework\UrlInterface;

class MessageAction extends Text
{
    /**
     * @var Url
     */
    protected $urlBuilder;

    /**
     * @param Context $context
     * @param Url $url
     */
    public function __construct(
        Context $context,
        Url $url
    ) {
        $this->urlBuilder   = $url;
        parent::__construct($context);
    }

    /**
     * get value
     *
     * @param DataObject $row
     * @return string
     */
    public function _getValue(DataObject $row): string
    {
        $editUrl = $this->urlBuilder->getUrl(
            'formbuilder/message/edit',
            ['message_id' => $row['message_id']]
        );
        return sprintf("<a target='_blank' href='%s'>Edit</a>", $editUrl);
    }
}
