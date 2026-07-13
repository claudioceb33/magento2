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

namespace Lof\Formbuilder\Block\Adminhtml\Blacklist\Edit\Tab;

use Lof\Formbuilder\Helper\Data;
use Lof\Formbuilder\Model\Blacklist;
use Lof\Formbuilder\Model\Message;
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
    protected $systemStore;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ObjectConverter
     */
    protected $objectConverter;

    /**
     * @var Message
     */
    protected $messageModel;

    /**
     * @var Blacklist
     */
    protected $blacklistModel;

    /**
     * @var Data
     */
    protected $formHelper;

    protected $templatesFactory;

    protected $emailConfig;

    /**
     * Detail constructor.
     * @param Context $context
     * @param GroupRepositoryInterface $groupRepository
     * @param ObjectConverter $objectConverter
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Store $systemStore
     * @param CollectionFactory $templatesFactory
     * @param Config $emailConfig
     * @param Blacklist $blacklist
     * @param Data $formHelper
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        GroupRepositoryInterface $groupRepository,
        ObjectConverter $objectConverter,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Store $systemStore,
        CollectionFactory $templatesFactory,
        Config $emailConfig,
        Blacklist $blacklist,
        Data $formHelper,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->groupRepository = $groupRepository;
        $this->objectConverter = $objectConverter;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->templatesFactory = $templatesFactory;
        $this->emailConfig = $emailConfig;
        $this->blacklistModel = $blacklist;
        $this->formHelper = $formHelper;
        parent::__construct($context, $data);
        if ($this->hasData("template") && $this->getData("template")) {
            $this->setTemplate($this->getData("template"));
        } elseif (isset($data['template']) && $data['template']) {
            $this->setTemplate($data['template']);
        } else {
            $this->setTemplate("Lof_Formbuilder::edit/blacklist.phtml");
        }
    }

    /**
     * set message model
     * @param $message
     * @return $this
     */
    public function setMessageModel($message)
    {
        $this->messageModel = $message;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toHtml()
    {
        $messageModel = $this->messageModel;
        $blacklist = [];
        $emails = [];
        if ($messageModel->getId()) {
            $blacklist = $this->blacklistModel->loadListByMessageId($messageModel->getId());
            $params = $messageModel->getParams();
            $params = $this->formHelper->decodeData($params);
            if ($params && isset($params['submit_data']) && $params['submit_data']) {
                $emails = $this->formHelper->getEmailsFromData($params['submit_data']);
            }
        }
        $this->assign("blacklist", $blacklist);
        $this->assign("messageModel", $messageModel);
        $this->assign("emails", $emails);
        return parent::toHtml();
    }

    /**
     * get blacklist url
     *
     * @return string
     */
    public function getBlacklistUrl(): string
    {
        return $this->getUrl("*/blacklist/ajaxblock");
    }

}
