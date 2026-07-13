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

namespace Lof\Formbuilder\Model\ResourceModel;

use Lof\Formbuilder\Helper\Data;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManagerInterface;

class Message extends AbstractDb
{
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    protected static int $handleTrackLinkCounter = 1;

    protected Data $helper;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Data $helper
     * @param $connectionName
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Data $helper,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->storeManager = $storeManager;
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('lof_formbuilder_message', 'message_id');
    }

    /**
     * @param AbstractModel $object
     * @return $this|Message
     */
    protected function _beforeSave(AbstractModel $object): Message|static
    {
        self::$handleTrackLinkCounter++;
        if ($object->getQrcode() == null) {
            $useLongcode = $this->helper->getConfig('message_setting/use_longcode');
            $secretKey = $this->helper->getConfig('message_setting/secret_key');
            $qrcode = substr(md5(microtime()), rand(0, 26), 6);
            if ($useLongcode) {
                $qrcode = sha1($qrcode . $secretKey);
            } else {
                $qrcode = $this->helper->generateTrackcode();
            }
            $object->setQrcode($qrcode);
        }
        return $this;
    }
}
