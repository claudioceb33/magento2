<?php

declare(strict_types=1);

namespace Ceb\ViewModelCustom\Model\Service;

class TopicCatalog
{

    /**
     * @var string
     */
    private $areaLabel;

    /**
     * Virtual type argument:
     * di.xml can reuse this same class with different constructor arguments
     * without creating a new PHP subclass.
     *
     * ES: Argumento de virtual type:
     * di.xml puede reutilizar esta misma clase con distintos argumentos de
     * constructor sin crear una nueva subclase PHP.
     */
    public function __construct(
        $areaLabel = 'global'
    ) {
    }

    /**
     * @return string[]
     */
    public function all(): array
    {
        return [
            'Bootstrap',
            'Object Manager',
            'Dependency Injection',
            'Preference',
            'Virtual Types',
            'Proxy',
            'Factory',
            'Plugin',
            'Observer',
            'Cron',
            'Queue',
            'Consumer',
            'Publisher',
            'Events',
            'Repositories',
            'Service Contracts',
            'ResourceModel',
            'Collections',
            'Models',
            'ViewModels',
            'UI Components',
            'RequireJS',
            'Knockout',
            'CustomerData',
            'Section Pool',
            'Cache',
            'Indexer',
            'Message Queue',
            'Web API',
            'GraphQL',
            'ACL',
            'Layouts',
            'XML Merge',
            'DI Compile',
            'Area',
            'Frontend',
            'Adminhtml',
            'Webapi_rest',
            'Webapi_soap',
            'Crontab',
            'CLI',
        ];
    }

    public function getAreaLabel(): string
    {
        return $this->areaLabel;
    }
}
