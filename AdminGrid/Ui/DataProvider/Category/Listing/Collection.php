<?php
namespace Ceb\AdminGrid\Ui\DataCeb\Category\Listing;

use Magento\Framework\View\Element\UiComponent\DataCeb\SearchResult;

class Collection extends SearchResult
{
    /**
     * Override _initSelect to add custom columns
     *
     * @return void
     */
    protected function _initSelect()
    {
        $this->addFilterToMap('entity_id', 'main_table.entity_id');
        $this->addFilterToMap('name', 'modulenamegridname.value');
        parent::_initSelect();
    }
}
