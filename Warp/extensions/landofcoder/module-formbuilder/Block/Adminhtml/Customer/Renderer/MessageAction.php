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

namespace Lof\Formbuilder\Block\Adminhtml\Customer\Renderer;

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
     * MessageAction constructor.
     * @param Context $context
     * @param Url $urlBuilder
     */
    public function __construct(
        Context $context,
        Url $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context);
    }

    /**
     * @param DataObject|mixed $row
     * @return string
     */
    public function _getValue($row)
    {
        $editUrl = $this->urlBuilder->getUrl(
            'formbuilder/message/edit',
            [
                'message_id' => $row['message_id']
            ]
        );
        return sprintf("<a target='_blank' href='%s'>View</a>", $editUrl);
    }
}
