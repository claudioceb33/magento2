<?php

namespace Swissup\SeoUrls\Model\Filter;

class Rating extends AbstractPredefined
{
    /**
     * {@inheritdoc}
     */
    protected function getDefaultLabel()
    {
        return $this->helper->getPredefinedFilterLabel('rating_filter');
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultOptions()
    {
        return [
                '80-100' => $this->helper->getSeoFriendlyString(__('4 and up')),
                '60-100' => $this->helper->getSeoFriendlyString(__('3 and up')),
                '40-100' => $this->helper->getSeoFriendlyString(__('2 and up')),
                '20-100' => $this->helper->getSeoFriendlyString(__('1 and up')),
                '0-100'  => $this->helper->getSeoFriendlyString(__('any')),
            ];
    }
}
