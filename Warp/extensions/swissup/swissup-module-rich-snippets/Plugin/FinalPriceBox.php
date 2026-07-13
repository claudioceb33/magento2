<?php
namespace Swissup\RichSnippets\Plugin;

class FinalPriceBox extends AbstractPlugin
{
    /**
     * Plugin before method renderAmount
     *
     * @param  \Magento\Catalog\Pricing\Render\FinalPriceBox $subject
     * @param  \Magento\Framework\Pricing\Amount\AmountInterface $amount
     * @param  array $arguments
     * @return null|array
     */
    public function beforeRenderAmount(
        \Magento\Catalog\Pricing\Render\FinalPriceBox $subject,
        \Magento\Framework\Pricing\Amount\AmountInterface $amount,
        array $arguments = []
    ){
        if ($subject->getZone() == 'item_view'
            && isset($arguments['schema'])
            && $this->isMicrodataDisabled()
        ) {
            $arguments['schema'] = false;
            return [$amount, $arguments];
        }

        return null;
    }
}
