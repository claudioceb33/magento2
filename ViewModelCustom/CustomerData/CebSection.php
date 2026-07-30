<?php

declare(strict_types=1);

namespace Ceb\ViewModelCustom\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

class CebSection implements SectionSourceInterface
{
    /**
     * CustomerData + Section Pool:
     * SectionSourceInterface returns private browser-stored data. frontend
     * di.xml registers this source in Magento's Section Pool.
     *
     * ES: CustomerData + Section Pool:
     * SectionSourceInterface devuelve datos privados guardados en el navegador.
     * frontend di.xml registra esta fuente en el Section Pool de Magento.
     *
     * @return array<string, string>
     */
    public function getSectionData(): array
    {
        return [
            'title' => 'Ceb Magento Section Data',
            'message' => 'Loaded through customer-data section.',
        ];
    }
}
