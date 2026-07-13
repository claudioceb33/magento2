<?php
namespace Lof\Formbuilder\Block\Adminhtml\Message\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject as ObjectConverter;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;

class Email extends Generic implements TabInterface
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
     * @var Config
     */
    protected Config $wysiwygConfig;
    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $templatesFactory;
    protected \Magento\Email\Model\Template\Config $emailConfig;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param GroupRepositoryInterface $groupRepository
     * @param ObjectConverter $objectConverter
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Store $systemStore
     * @param Config $wysiwygConfig
     * @param CollectionFactory $templatesFactory
     * @param \Magento\Email\Model\Template\Config $emailConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        GroupRepositoryInterface $groupRepository,
        ObjectConverter $objectConverter,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Store $systemStore,
        Config $wysiwygConfig,
        CollectionFactory $templatesFactory,
        \Magento\Email\Model\Template\Config $emailConfig,
        array $data = []
    ) {
        $this->wysiwygConfig = $wysiwygConfig;
        $this->systemStore = $systemStore;
        $this->groupRepository = $groupRepository;
        $this->objectConverter = $objectConverter;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->templatesFactory = $templatesFactory;
        $this->emailConfig = $emailConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm(): Email
    {
        return parent::_prepareForm();
    }

    /**
     * @inheritdoc
     */
    public function toHtml()
    {
        $model = $this->_coreRegistry->registry('formbuilder_message');
        $html  = $model->getMessage();

        if ($model->getId()) {
            $html = '
                <div class="reply-wrapper" style="font-weight: bold;padding: 20px 0;">'
                . $this->getLayout()->createBlock(Detail::class)
                    ->setMessageModel($model)->toHtml() . '</div>';
        }
        return $html;
    }

    /**
     * Prepare label for tab
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Reply Email');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Reply Email');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden(): bool
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function isAllowedAction(string $resourceId): bool
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
