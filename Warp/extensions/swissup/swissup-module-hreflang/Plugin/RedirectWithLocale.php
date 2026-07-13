<?php
/**
 * Plugin for \Magento\Cms\Controller\Index\Index
 */
namespace Swissup\Hreflang\Plugin;

use Magento\Framework\Controller\Result\RedirectFactory ;

class RedirectWithLocale extends AbstractPlugin
{
    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @param \Swissup\Hreflang\Helper\Store $helper
     * @param RedirectFactory                $redirectFactory
     */
    public function __construct(
        \Swissup\Hreflang\Helper\Store $helper,
        RedirectFactory $redirectFactory
    ) {
        parent::__construct($helper);
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * Force redirect to url with locale in it when enabled
     *
     * @param  \Magento\Cms\Controller\Index\Index $subject
     * @param  mixed                              $result
     * @return mixed
     */
    public function afterExecute(
        \Magento\Cms\Controller\Index\Index $subject,
        $result
    ) {
        if ($this->helper->isRedirectAllowed()) {
            $store = $this->helper->getStoreManager()->getStore();
            $redirect = $this->redirectFactory->create();
            $redirect->setUrl($store->getCurrentUrl(false));
            $redirect->setHttpResponseCode(301);
            return $redirect;
        }

        return $result;
    }
}
