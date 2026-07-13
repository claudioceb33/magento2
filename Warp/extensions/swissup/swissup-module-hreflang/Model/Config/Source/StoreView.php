<?php

namespace Swissup\Hreflang\Model\Config\Source;

class StoreView implements \Magento\Framework\Option\ArrayInterface
{
    const NOT_SPECIFIED = 0;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $stores = $this->storeManager->getStores();

        $options = [
            [
                'value' => self::NOT_SPECIFIED,
                'label' => __('- Not Specified -')
            ]
        ];
        foreach ($stores as $store) {
            $options[] = [
                'value' => $store->getId(),
                'label' => $store->getName()
            ];
        }

        return $options;
    }
}
