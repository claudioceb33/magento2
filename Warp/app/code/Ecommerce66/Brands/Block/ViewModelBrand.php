<?php

namespace Ecommerce66\Brands\Block;

use Ecommerce66\Brands\ViewModel\BrandData;
use Magento\Framework\View\Element\Template;

class ViewModelBrand extends Template
{
    protected $viewModel;

    public function __construct(
        Template\Context $context,
        BrandData $viewModel,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->viewModel = $viewModel;
    }

    public function getViewModelBrand(): BrandData
    {
        return $this->viewModel;
    }
}
