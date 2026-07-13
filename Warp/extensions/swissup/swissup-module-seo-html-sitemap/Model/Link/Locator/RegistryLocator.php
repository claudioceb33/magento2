<?php
namespace Swissup\SeoHtmlSitemap\Model\Link\Locator;

use Swissup\SeoHtmlSitemap\Model\Link;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;

class RegistryLocator implements LocatorInterface
{
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var \Swissup\SeoHtmlSitemap\Model\Link
     */
    private $link;
    /**
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }
    /**
     * {@inheritdoc}
     * @throws NotFoundException
     */
    public function getLink()
    {
        if (null !== $this->link) {
            return $this->link;
        }
        if ($link = $this->registry->registry('seohtmlsitemap_link')) {
            return $this->link = $link;
        }
        throw new NotFoundException(__('Link was not registered'));
    }
}
