<?php

namespace Swissup\AddressFieldManager\Observer;

class AddPageClassName implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * @var \Swissup\AddressFieldManager\Model\ResourceModel\Customer\Form\AddressAttribute\CollectionFactory
     */
    protected $collectionFactory;

    /**
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\View\Page\Config $pageConfig,
        \Swissup\AddressFieldManager\Model\ResourceModel\Customer\Form\AddressAttribute\CollectionFactory $collectionFactory
    ) {
        $this->request = $request;
        $this->pageConfig = $pageConfig;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Hide address fields, that are hardcoded in template.
     * Used at customer edit address page.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->canAddPageClasses()) {
            return;
        }

        foreach ($this->getClassNames() as $class) {
            $this->pageConfig->addBodyClass($class);
        }
    }

    /**
     * Checks if requested page should be processed
     *
     * @return boolean
     */
    protected function canAddPageClasses()
    {
        return $this->request->getFullActionName() === 'customer_address_form';
    }

    /**
     * Get array of hidden fields and return appropriate class names.
     *
     * @return array
     */
    protected function getClassNames()
    {
        $classNames = [];
        $collection = $this->collectionFactory->create();
        // @todo: replace `ca` with `sa`, when per website support will be added
        $collection->addFieldToFilter('ca.is_visible', 0);
        $configurableFields = [
            'prefix', 'middlename', 'suffix', 'vat_id'
        ];
        foreach ($collection as $attribute) {
            $code = $attribute->getAttributeCode();
            // no need to indicate configurable fields - Magento will hide them automatically.
            if (in_array($code, $configurableFields)) {
                continue;
            }

            $classNames[] = 'afm-hidden-' . $code;
        }
        return $classNames;
    }
}
