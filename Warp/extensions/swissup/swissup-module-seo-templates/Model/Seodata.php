<?php

namespace Swissup\SeoTemplates\Model;

use Magento\Framework\App\ObjectManager;

class Seodata extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Swissup\SeoTemplates\Model\ResourceModel\Seodata::class);
    }

    /**
     * Delete all generated data for entity types in $entityTypes
     *
     * @param  array  $entityTypes
     * @param  array  $entityIds
     * @return $this
     */
    public function deleteGenerated($entityTypes = [], $entityIds = [])
    {
        $this->getResource()->deleteGenerated($entityTypes, $entityIds);
        return $this;
    }

    public function unserialize()
    {
        $resource = $this->_getResource();
        $resource->unserializeFields($this);

        return $this;
    }

    public function getMetadata()
    {
        $data = array_map(function ($data) {
            $objectManager = ObjectManager::getInstance();
            $value = $data['value'] ?? '';
            $conditional = array_map(function ($item) use ($objectManager) {
                $condition = $objectManager
                    ->create(
                        $item['condition']['type'],
                        ['data' => ['prefix' => 'storefront_cond']]
                    )
                    ->loadArray($item['condition']);
                $item['condition'] = $condition;

                return $item;
            }, $data['conditional'] ?? []);

            $priority = array_column($conditional, 'priority');
            array_multisort($priority, SORT_DESC, SORT_NUMERIC, $conditional);

            return [
                'value' => $value,
                'conditional' => $conditional
            ];
        }, $this->getData('metadata'));

        return $data;
    }
}
