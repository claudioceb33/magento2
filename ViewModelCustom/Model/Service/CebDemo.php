<?php

declare(strict_types=1);

namespace Ceb\ViewModelCustom\Model\Service;

use Ceb\ViewModelCustom\Api\CebInterface;
use Magento\Framework\Event\ManagerInterface;

class CebDemo implements CebInterface
{
    private $topicCatalog;

    private $heavyReportGenerator;

    private $eventManager;

    public function __construct(
        TopicCatalog $topicCatalog,
        HeavyReportGenerator $heavyReportGenerator,
        ManagerInterface $eventManager
    ) {
    }

    /**
     * Dependency Injection, events, proxy and plugin target:
     * Dependencies are constructor-injected; a custom event is dispatched; the
     * heavy service is lazy through a proxy; and a plugin decorates this result.
     *
     * ES: Dependency Injection, eventos, proxy y objetivo de plugin:
     * Las dependencias se inyectan por constructor; se dispara un evento custom;
     * el servicio pesado es lazy mediante proxy; y un plugin decora este resultado.
     *
     * @return string[]
     */
    public function summarize(): array
    {
        $summary = [
            'title' => 'Ceb Magento',
            'area' => $this->topicCatalog->getAreaLabel(),
            'lazy_report' => $this->heavyReportGenerator->build(),
            'topics' => implode(', ', $this->topicCatalog->all()),
        ];

        $this->eventManager->dispatch('ceb_magento_demo_ran', ['summary' => $summary]);

        return $summary;
    }
}
