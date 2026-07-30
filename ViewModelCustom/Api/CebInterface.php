<?php

declare(strict_types=1);

namespace Ceb\ViewModelCustom\Api;

/**
 * Web API service contract:
 * Public service interfaces in Api can be exposed through webapi.xml and reused
 * by PHP clients, REST, SOAP and integration code.
 *
 * ES: Service contract para Web API:
 * Las interfaces publicas en Api pueden exponerse mediante webapi.xml y
 * reutilizarse desde clientes PHP, REST, SOAP e integraciones.
 */
interface CebInterface
{
    /**
     * @return string[]
     */
    public function summarize(): array;
}
