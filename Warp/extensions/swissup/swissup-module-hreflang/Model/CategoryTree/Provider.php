<?php

namespace Swissup\Hreflang\Model\CategoryTree;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Categories as ProductModifierCategories;
use Magento\Framework\Registry;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Swissup\Hreflang\Helper\Store as Helper;

class Provider
{
    private Helper $helper;
    private ObjectManagerInterface $objectManager;
    private Registry $registry;
    private RequestInterface $request;

    public function __construct(
        Helper $helper,
        ObjectManagerInterface $objectManager,
        Registry $registry,
        RequestInterface $request
    ) {
        $this->helper = $helper;
        $this->objectManager = $objectManager;
        $this->registry = $registry;
        $this->request = $request;
    }

    public function provide(): ?array
    {
        return $this->getCategoriesTree();
    }

    private function getCategoriesTree(): ?array
    {
        $this->registerStore();
        $modifierCategories = $this->objectManager
            ->get(ProductModifierCategories::class);
        $reflectedClass = new \ReflectionClass($modifierCategories);
        $method = $reflectedClass->getMethod('getCategoriesTree');
        $method->setAccessible(true);

        return $method->invoke($modifierCategories);
    }

    private function registerStore(): void
    {
        $storeId = $this->request->getParam('store', 0);
        $store = $this->helper->getStoreManager()->getStore($storeId);
        $this->registry->unregister('current_store');
        $this->registry->register('current_store', $store);
    }
}
