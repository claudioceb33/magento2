<?php

namespace Swissup\SeoTemplates\Model\Indexer;

use Swissup\SeoTemplates\Model\Generator;

abstract class AbstractAction implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var Generator
     */
    protected $generator;

    /**
     * @var integer
     */
    protected $batchSize;

    /**
     * @param Generator $generator
     * @param integer   $batchSize
     */
    public function __construct(
        Generator $generator,
        $batchSize = 100
    ) {
        $this->generator = $generator;
        $this->batchSize = $batchSize;
    }

    /**
     * Execute
     *
     * @param null|int|array $ids
     * @return void
     */
    public function execute($ids)
    {
        $ids = is_array($ids) ? $ids : [$ids];
        $entityType = $this->getEntityType();
        $this->generator->clearGeneratedData([$entityType], $ids);
        $this->generator->claerTemplatesLogs([$entityType], $ids);
        $this->generator
            ->setPageSize($this->batchSize)
            ->setEntityType($entityType);

        $page = 1;
        do {
            $this->generator->setCurPage($page)->generate($ids);
            $page = $this->generator->getNextPage();
        } while ($page);
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->execute([]);
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
         $this->execute([$id]);
    }

    /**
     * @return int
     */
    abstract public function getEntityType();
}
