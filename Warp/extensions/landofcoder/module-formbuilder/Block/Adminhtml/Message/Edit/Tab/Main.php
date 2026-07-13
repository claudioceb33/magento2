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

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject as ObjectConverter;
use Lof\Formbuilder\Helper\Data;
use Lof\Formbuilder\Helper\Barcode;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;

class Main extends Generic implements TabInterface
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
     * @var Data
     */
    protected Data $helperData;

    /**
     * @var Barcode
     */
    protected Barcode $barcodeData;
    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $templatesFactory;
    /**
     * @var Config
     */
    protected Config $emailConfig;

    /**
     * Main constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param GroupRepositoryInterface $groupRepository
     * @param ObjectConverter $objectConverter
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Store $systemStore
     * @param CollectionFactory $templatesFactory
     * @param Config $emailConfig
     * @param Data $helperData
     * @param Barcode $barcodeData
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
        Data $helperData,
        Barcode $barcodeData,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->groupRepository = $groupRepository;
        $this->objectConverter = $objectConverter;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->templatesFactory = $templatesFactory;
        $this->emailConfig = $emailConfig;
        $this->helperData = $helperData;
        $this->barcodeData = $barcodeData;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    public function toHtml()
    {
        $model = $this->_coreRegistry->registry('formbuilder_message');
        if ($model['creation_time']) {
            $model['creation_time'] = $this->helperData->formatDateFormBuilder($model['creation_time']);
        }
        $qrcode = $model->getQrcode();
        $qrcode_tracking_link = $this->helperData->getQrcodeTracklink($model);
        $barcode_link = $this->barcodeData->generateBarcodeLabel($model, false);
        $track_url = $this->helperData->getTrackUrl($model);

        $html = $this->helperData->xssClean($model->getMessage());
        $html .= __('<div style="font-weight: bold;padding: 20px 0;">Create At: %1</div>', $model->getCreationTime());
        if ($qrcode) {
            $barcode_html = "";
            if ($barcode_link) {
                $barcode_html = '
                <br/>
                <p>
                    <img src="' . $barcode_link . '" width="200" height="60" style="height: 60px" alt="barcode"/>
                </p>
                <p class="text-center">
                    <span>' . $qrcode . '</span>
                </p>
                ';
            }
            $html .= '<div class="qrcode">';
            $html .= '<table class="main">
            <tr>
              <td>
                <p>
                    <img src="' . $qrcode_tracking_link . '" width="200" height="200" alt="qrcode"/>
                </p>
                <p><strong>' . __("QR Code:") . '</strong> <em>' . $qrcode . '</em></p>
                ' . $barcode_html . '
              </td>
            </tr>
            <tr>
              <td class="email-tracklink">
                ' . __("You can view the submitted form message at here:") . ' <a href="' . $track_url . '">' . __("View Message") . '</a>.
              </td>
            </tr>
            </table>';
            $html .= '</div>';
        }
        if ($model->getId()) {
            $html .= '<div class="blacklist-wrapper" style="font-weight: bold;padding: 20px 0;">' . $this->getLayout()
                    ->createBlock(\Lof\Formbuilder\Block\Adminhtml\Blacklist\Edit\Tab\Detail::class)
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
        return __('Message Information');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Message Information');
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
