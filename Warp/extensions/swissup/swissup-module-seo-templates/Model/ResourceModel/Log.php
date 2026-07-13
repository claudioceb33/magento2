<?php

namespace Swissup\SeoTemplates\Model\ResourceModel;

class Log extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_seotemplates_log', 'id');
    }

    /**
     * @param  array  &$data
     * @return $this
     */
    public function insertData(array &$data)
    {
        if (empty($data)) {
            return $this;
        }

        $this->getConnection()->insertMultiple($this->getMainTable(),$data);

        return $this;
    }
}
