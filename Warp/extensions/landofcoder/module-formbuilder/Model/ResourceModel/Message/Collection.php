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
declare(strict_types=1);

namespace Lof\Formbuilder\Model\ResourceModel\Message;

use \Lof\Formbuilder\Model\ResourceModel\AbstractCollection;
use Lof\Formbuilder\Model\ResourceModel\Message;
use Magento\Store\Model\Store;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'message_id';

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad(): static
    {
        $this->getFormNameAfterLoad();
        $this->setSafedMessageText();
        return parent::_afterLoad();
    }

    /**
     * Define resource model
     *
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(\Lof\Formbuilder\Model\Message::class, Message::class);
        $this->_map['fields']['form_name'] = 'form_table.title';
        $this->addFilterToMap('form_name', 'form_table.title');
    }

    protected function getFormNameAfterLoad()
    {
        $items = $this->getColumnValues("message_id");
        if (count($items)) {
            $connection = $this->getConnection();
            foreach ($this as $item) {
                $formId = $item->getData('form_id');
                if (empty($formId)) {
                    $item->setData('form_name', '');
                } else {
                    $select = $connection->select()
                        ->from(['form' => $this->getTable('lof_formbuilder_form')])
                        ->where('form.form_id = (?)', $formId);
                    $result = $connection->fetchRow($select);
                    $item->setData('form_name', $result['title']);
                }
            }
        }
    }

    /**
     * Add filter by store
     *
     * @param array|int|Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter(Store|array|int $store, bool $withAdmin = true): static
    {
        $this->performAddStoreFilter($store, $withAdmin);

        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore(): void
    {
        $this->joinFormRelationTable('lof_formbuilder_form', 'form_id');
        parent::_renderFiltersBefore();
    }
    protected function setSafedMessageText()
    {
        foreach ($this as $item) {
            $message = $item->getData("message");
            if (!$message) {
                continue;
            }
            $safedMessage = $item->getSafedMessage($message, true);
            $item->setData('safe_message', $safedMessage);
        }
    }
}
