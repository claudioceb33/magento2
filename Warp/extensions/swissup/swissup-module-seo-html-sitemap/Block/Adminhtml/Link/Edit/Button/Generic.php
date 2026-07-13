<?php
namespace Swissup\SeoHtmlSitemap\Block\Adminhtml\Link\Edit\Button;

use Swissup\SeoHtmlSitemap\Model\Link\Locator\LocatorInterface;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Generic
{
    /**
     * @var Context
     */
    protected $context;

     /**
     * @var Locator
     */
    protected $locator;

    /**
     * Generic Constructor
     *
     * @param Context $context
     */
    public function __construct(
        Context $context,
        LocatorInterface $locator
    ) {
        $this->context = $context;
        $this->locator = $locator;
    }

    public function getButtonData()
    {
        return [];
    }

    /**
     * Return Link ID
     *
     * @return int|null
     */
    public function getLinkId()
    {
        if ($link = $this->getLink()) {
            return $link->getId();
        }
        return null;
    }

    /**
     * Return Link
     *
     * @return \Swissup\SeoHtmlSitemap\Model\Link|null
     */
    public function getLink()
    {
        return $this->locator->getLink();
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrl($route, $params);
    }
}
