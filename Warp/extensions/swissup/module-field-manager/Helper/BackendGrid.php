<?php

namespace Swissup\FieldManager\Helper;

use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class Backend is used to allow to use store switcher together with
 * grid mass actions and UI Data Provider:
 *
 * @see \Swissup\FieldManager\Controller\Adminhtml\Index\Index
 * @see \Swissup\FieldManager\Controller\Adminhtml\Index\MassStatus
 * @see \Swissup\FieldManager\Ui\DataProvider\Attributes\Listing
 */
class BackendGrid
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * Backend constructor
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(DataPersistorInterface $dataPersistor)
    {
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getCurrentWebsiteId($key)
    {
        return $this->dataPersistor->get($key);
    }

    /**
     * @param string $key
     * @param integer $id
     */
    public function setCurrentWebsiteId($key, $id)
    {
        $this->dataPersistor->set($key, $id);
    }
}
