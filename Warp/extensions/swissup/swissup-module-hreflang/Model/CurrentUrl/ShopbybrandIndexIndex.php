<?php

namespace Swissup\Hreflang\Model\CurrentUrl;

use Magento\Framework\Registry;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Swissup\SeoCore\Model\CurrentUrl\ProviderInterface;

class ShopbybrandIndexIndex implements ProviderInterface
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\Helper\AbstractHelper
     */
    protected $brandHelper;

    /**
     * @var array
     */
    private $isActiveData = [];

    /**
     * __construct
     *
     * @param Registry               $registry
     * @param RequestInterface       $request
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        Registry $registry,
        RequestInterface $request,
        ObjectManagerInterface $objectManager
    ) {
        $this->registry = $registry;
        $this->request = $request;
        $this->objectManager = $objectManager;
        $this->brandHelper = $objectManager->get('\Magezon\ShopByBrand\Helper\Data');
    }

    /**
     * {@inheritdoc}
     */
    public function provide(
        \Magento\Store\Model\Store $store,
        $queryParamsToUnset = []
    ) {
        $pathInfo = $this->request->getAlias(
            \Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS
        );

        $newPathInfo = $this->getNewPathInfo($store);
        if (!$newPathInfo) {
            return null;
        }

        $url = $store->getCurrentUrl(false, $queryParamsToUnset);
        return $pathInfo == $newPathInfo
            ? $url
            : str_replace('/' . $pathInfo, '/' . $newPathInfo, $url);
    }

    /**
     * @param  \Magento\Store\Model\Store $store
     * @return string|null
     */
    protected function getNewPathInfo(
        \Magento\Store\Model\Store $store
    ) {
        $isEnabled = $this->brandHelper->getConfig('general/enable', $store);
        if (!$isEnabled) {
            return null;
        }

        return $this->brandHelper->getConfig('general/route', $store);
    }

    /**
     * Checks if entity (brand/category) active at specified store view with ID $storeId
     *
     * @param  \Magento\Framework\Model\AbstractModel
     * @param  int
     * @return boolean
     */
    public function isActive(
        \Magento\Framework\Model\AbstractModel $entity,
        $storeId
    ) {
        $isActive = $this->getIsActiveData($entity);

        return $isActive[(int)$storeId] ?? $isActive[0];
    }

    /**
     * @param  \Magento\Framework\Model\AbstractModel
     * @return array
     */
    private function getIsActiveData(\Magento\Framework\Model\AbstractModel $entity) {
        $id = $entity->getId();
        if (!isset($this->isActiveData[$id])) {
            $attribute = $entity->getResource()->getAttribute('is_active');
            $linkField = $attribute->getEntity()->getLinkField();
            $linkValue = $entity->getData($linkField);
            $connection = $entity->getResource()->getConnection();
            $select = $connection->select()
                ->from(
                    $attribute->getBackend()->getTable(),
                    ['store_id', 'value']
                )
                ->where("{$linkField} = ?", $linkValue)
                ->where('attribute_id = ?', $attribute->getId());

            $this->isActiveData[$id] = $connection->fetchPairs($select);
        }

        return $this->isActiveData[$id];
    }
}
