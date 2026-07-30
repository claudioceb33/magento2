<?php

declare(strict_types=1);

namespace Ceb\ViewModelCustom\Model\Service;

class HeavyReportGenerator
{
    /**
     * Proxy target:
     * This service represents an expensive collaborator. di.xml injects its
     * generated Proxy so the real object is created only when build() is called.
     *
     * ES: Objetivo de proxy:
     * Este servicio representa un colaborador costoso. di.xml inyecta su Proxy
     * generado para que el objeto real se cree solo cuando se llama build().
     */
    public function build(): string
    {
        return 'Heavy report generated lazily through a proxy.';
    }
}
