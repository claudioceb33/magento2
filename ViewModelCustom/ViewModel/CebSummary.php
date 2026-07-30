<?php

declare(strict_types=1);

namespace Ceb\ViewModelCustom\ViewModel;

use Ceb\ViewModelCustom\Api\CebInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class CebSummary implements ArgumentInterface
{
    /**
     * 
     */
    private $cebDemo;

    public function __construct(
        CebInterface $cebDemo
    ) {
        $this->cebDemo = $cebDemo;
    }

    /**
     * ViewModel:
     * ViewModels expose presentation data to templates without putting business
     * logic inside blocks or .phtml files.
     *
     * ES: ViewModel:
     * Los ViewModels exponen datos de presentacion a templates sin poner logica
     * de negocio dentro de blocks o archivos .phtml.
     *
     * @return string[]
     */
    public function getSummary(): array
    {
        return $this->cebDemo->summarize();
    }
}
