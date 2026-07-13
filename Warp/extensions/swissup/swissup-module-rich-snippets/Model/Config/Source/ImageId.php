<?php

namespace Swissup\RichSnippets\Model\Config\Source;

use Magento\Catalog\Helper\Image;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\View as ConfigView;
use Magento\Store\Model\App\Emulation;

class ImageId implements \Magento\Framework\Option\ArrayInterface
{
    private ConfigView $configView;
    private RequestInterface $request;
    private Emulation $emulation;

    public function __construct(
        RequestInterface $request,
        Emulation $emulation
    ) {
        $this->request = $request;
        $this->emulation = $emulation;
        $this->configView = $this->getConfigView();
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $mediaEntries = $this->configView->getMediaEntities(
            'Magento_Catalog',
            Image::MEDIA_TYPE_CONFIG_NODE
        );
        $mediaEntries = array_filter($mediaEntries, function ($entry) {
            return $entry['type'] === 'image';
        });

        return array_map(function ($entry, $key) {
            $h = $entry['height'] ?? '';
            $w = $entry['width'] ?? '';

            $label = "{$key} ({$w}x{$h})";
            $label = str_replace(' (x)', '', $label);
            $label = str_replace('_', ' ', $label);
            $label = ucfirst($label);

            return [
                'value' => $key,
                'label' => $label
            ];
        }, $mediaEntries, array_keys($mediaEntries));
    }

    private function getStoreId(): ?int {
        $store = $this->request->getParam('store');

        return $store ? (int)$store : null;
    }

    private function getConfigView() {
        $objectManager = ObjectManager::getInstance();
        $this->emulation->startEnvironmentEmulation(
            $this->getStoreId(),
            \Magento\Email\Model\AbstractTemplate::DEFAULT_DESIGN_AREA,
            true
        );
        try {
            $configView = $objectManager->create(
                '\Magento\Framework\Config\View'
            );
            $configView->read();
        } catch (\Exception $e) {
            throw $e;
        } finally {
            $this->emulation->stopEnvironmentEmulation();
        }

        return $configView;
    }
}
