<?php

declare(strict_types=1);

namespace Ceb\ErpStockSync\Console\Command;

use Ceb\ErpStockSync\Api\StockSynchronizerInterface;
use Ceb\ErpStockSync\Model\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncStockCommand extends Command
{
    private const COMMAND_NAME = 'ceb:erp:stock:sync';

    public function __construct(
        private readonly StockSynchronizerInterface $stockSynchronizer,
        private readonly Config $config
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription(
                'Synchronize stock from the ERP.'
            );

        parent::configure();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        if (!$this->config->isEnabled()) {
            $output->writeln(
                '<error>ERP Stock Sync is disabled.</error>'
            );

            return Command::FAILURE;
        }

        $output->writeln('<info>ERP Stock Sync</info>');
        $output->writeln('====================');
        $output->writeln('');

        $output->writeln(
            sprintf(
                'ERP mode: %s',
                $this->config->getMode()
            )
        );

        $output->writeln(
            sprintf(
                'ERP page size: %d',
                $this->config->getErpPageSize()
            )
        );

        $output->writeln(
            sprintf(
                'Batch size: %d',
                $this->config->getBatchSize()
            )
        );

        $output->writeln(
            sprintf(
                'Dry run: %s',
                $this->config->isDryRun() ? 'Yes' : 'No'
            )
        );

        $output->writeln('');

        $startTime = microtime(true);

        $result = $this->stockSynchronizer->execute();

        $executionTime = microtime(true) - $startTime;

        $output->writeln('<info>Summary</info>');
        $output->writeln('-------');

        $output->writeln(
            sprintf(
                'Processed items: %d',
                $result->getProcessedItems()
            )
        );

        $output->writeln(
            sprintf(
                'Processed batches: %d',
                $result->getProcessedBatches()
            )
        );

        $output->writeln(
            sprintf(
                'Valid items: %d',
                $result->getValidItems()
            )
        );

        $output->writeln(
            sprintf(
                'Invalid items: %d',
                $result->getInvalidItems()
            )
        );

        $output->writeln(
            sprintf(
                'Updated items: %d',
                $result->getUpdatedItems()
            )
        );

        $output->writeln(
            sprintf(
                'Execution time: %.4f seconds',
                $executionTime
            )
        );

        $output->writeln(
            sprintf(
                'Peak memory: %.2f MB',
                memory_get_peak_usage(true) / 1024 / 1024
            )
        );

        return Command::SUCCESS;
    }
}
