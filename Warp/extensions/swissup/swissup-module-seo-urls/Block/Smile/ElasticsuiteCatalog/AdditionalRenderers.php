<?php

namespace Swissup\SeoUrls\Block\Smile\ElasticsuiteCatalog;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\LayeredNavigation\Block\Navigation\FilterRendererInterface;

class AdditionalRenderers extends AbstractBlock implements FilterRendererInterface
{
    /**
     * {@inheritdocs}
     */
    protected function _prepareLayout()
    {
        $seoHelper = $this->getData('seoHelper');
        if ($seoHelper->isModuleOutputEnabled('Smile_ElasticsuiteCatalog')) {
            $this->initializeRenderers();
        }

        return parent::_prepareLayout();
    }

    private function initializeRenderers(): void
    {
        $renderers = $this->getData('renderers') ?: [];
        $parentBlock = $this->getParentBlock();
        if ($parentBlock) {
            $layout = $this->getLayout();
            foreach ($renderers as $alias => $settings) {
                $block = $layout->addBlock(
                    $settings['class'],
                    "{$parentBlock->getNameInLayout()}.{$alias}",
                    $parentBlock->getNameInLayout()
                );
                $block->addData($settings['data'] ?: []);
                $siblingName = $settings['after'] ?? ($settings['before'] ?? false);
                $after = isset($settings['after']) ? true : false;
                if ($siblingName) {
                    $layout->reorderChild(
                        $parentBlock->getNameInLayout(),
                        $block->getNameInLayout(),
                        $siblingName,
                        $after
                    );
                }
            }
        }
    }

    public function canRender(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function render(FilterInterface $filter) {
        return '';
    }
}
