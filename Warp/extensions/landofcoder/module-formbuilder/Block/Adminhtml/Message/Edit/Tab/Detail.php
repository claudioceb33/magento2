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

namespace Lof\Formbuilder\Block\Adminhtml\Message\Edit\Tab;

use Lof\Formbuilder\Helper\Data;
use Lof\Formbuilder\Model\Message;
use Lof\Formbuilder\Model\Reply;
use Magento\Backend\Block\Template\Context;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject as ObjectConverter;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\System\Store;

class Detail extends Template
{
    /**
     * @var Store
     */
    protected Store $systemStore;

    /**
     * @var GroupRepositoryInterface
     */
    protected GroupRepositoryInterface $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var ObjectConverter
     */
    protected ObjectConverter $objectConverter;

    /**
     * @var Message
     */
    protected Message $messageModel;

    /**
     * @var Reply
     */
    protected Reply $replyModel;

    /**
     * @var Data
     */
    protected Data $formHelper;
    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $templatesFactory;
    /**
     * @var Config
     */
    protected Config $emailConfig;

    /**
     * Detail constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param GroupRepositoryInterface $groupRepository
     * @param ObjectConverter $objectConverter
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Store $systemStore
     * @param CollectionFactory $templatesFactory
     * @param Config $emailConfig
     * @param Reply $reply
     * @param Data $formHelper
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        GroupRepositoryInterface $groupRepository,
        ObjectConverter $objectConverter,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Store $systemStore,
        CollectionFactory $templatesFactory,
        Config $emailConfig,
        Reply $reply,
        Data $formHelper,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->groupRepository = $groupRepository;
        $this->objectConverter = $objectConverter;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->templatesFactory = $templatesFactory;
        $this->emailConfig = $emailConfig;
        $this->replyModel = $reply;
        $this->formHelper = $formHelper;
        parent::__construct($context, $data);
        if ($this->hasData("template") && $this->getData("template")) {
            $this->setTemplate($this->getData("template"));
        } elseif (isset($data['template']) && $data['template']) {
            $this->setTemplate($data['template']);
        } else {
            $this->setTemplate("Lof_Formbuilder::edit/reply.phtml");
        }
    }

    /**
     * @param $message
     * @return $this
     */
    public function setMessageModel($message)
    {
        $this->messageModel = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function toHtml(): string
    {
        $messageModel = $this->messageModel;
        $reply = [];
        $emails = [];
        if ($messageModel->getId()) {
            $reply = $this->replyModel->loadListByMessageId($messageModel->getId());
            $params = $messageModel->getParams();
            $params = $this->formHelper->decodeData($params);
            if ($params && isset($params['submit_data']) && $params['submit_data']) {
                $emails = $this->formHelper->getEmailsFromData($params['submit_data']);
            }
        }
        $this->assign("reply", $reply);
        $this->assign("messageModel", $messageModel);
        $this->assign("emails", $emails);

        return parent::toHtml();
    }

    /**
     * @param $data
     * @return array|string|null
     */
    public function xssClean($data)
    {
        return $this->formHelper->xssClean($data);
    }

    /**
     * @return string
     */
    public function getReplylistUrl()
    {
        return $this->getUrl("*/message/ajaxblock");
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * @param $data
     * @return string|string[]|null
     */
    public function xss_clean($data)
    {
        return $this->_formHelper->xss_clean($data);
    }

    /**
     * @param $date
     * @return false|string
     */
    public function format_date($date)
    {
        return $this->_formHelper->formatDateFormBuilder($date);
    }
}
