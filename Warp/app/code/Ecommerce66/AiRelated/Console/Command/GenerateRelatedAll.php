<?php
namespace Ecommerce66\AiRelated\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;
use Ecommerce66\AiRelated\Model\Generator;
use Ecommerce66\AiRelated\Helper\Data as AiHelper;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class GenerateRelatedAll extends Command
{
    protected static $defaultName = 'ecommerce66:ai:generate-related-all';
    private $generator;
    private $logger;
    private $aiHelper;
    private $lockManager;
    private $scopeConfig;

    public function __construct(
        Generator $generator,
        LoggerInterface $logger,
        AiHelper $aiHelper,
        LockManagerInterface $lockManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->generator = $generator;
        $this->logger = $logger;
        $this->aiHelper = $aiHelper;
        $this->lockManager = $lockManager;
        $this->scopeConfig = $scopeConfig;
        parent::__construct(self::$defaultName);
    }

    protected function configure()
    {
        $this->setDescription('Generate AI related for the entire catalog using an internal loop with lock')
            ->addOption('batch', null, InputOption::VALUE_OPTIONAL, 'Batch size for internal loop (overrides admin)', null)
            ->addOption('reset', null, InputOption::VALUE_NONE, 'Reset saved progress before run')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force override existing related links')
            ->addOption('lock-name', null, InputOption::VALUE_OPTIONAL, 'Lock name', 'ecommerce66_ai_generate_all_lock');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $adminBatch = (int)$this->scopeConfig->getValue('ecommerce66_ai/ai_related/generate_all_batch');
        $batch = $input->getOption('batch') !== null ? (int)$input->getOption('batch') : ($adminBatch > 0 ? $adminBatch : 100);
        $throttleSeconds = (int)$this->scopeConfig->getValue('ecommerce66_ai/ai_related/generate_all_throttle_seconds');
        $lockName = (string)$input->getOption('lock-name');
        $reset = (bool)$input->getOption('reset');
        $force = (bool)$input->getOption('force');

        if (!$this->isEnabled()) {
            $output->writeln('generate-related-all is disabled in admin config. Aborting.');
            return Command::SUCCESS;
        }

        if (!$this->acquireLock($lockName, $output)) {
            return Command::SUCCESS;
        }

        if ($reset) {
            $this->generator->resetProgress();
            $output->writeln('Progress reset.');
        }

        list($totalProcessed, $totalSaved, $totalSkipped) = $this->runLoop($batch, $force, $throttleSeconds, $output);

        $this->releaseLock($lockName);

        $output->writeln('Done. totalProcessed=' . $totalProcessed . ' totalSaved=' . $totalSaved . ' totalSkipped=' . $totalSkipped);
        $this->logger->info('AI Related All finished: ' . json_encode(['processed' => $totalProcessed, 'saved' => $totalSaved, 'skipped' => $totalSkipped]));

        return Command::SUCCESS;
    }

    public function isEnabled(): bool
    {
        try {
            $enabled = $this->scopeConfig->getValue('ecommerce66_ai/ai_related/generate_all_enable');
            return $enabled === null || $enabled != 0;
        } catch (\Throwable $e) {
            return true;
        }
    }

    private function acquireLock(string $lockName, OutputInterface $output): bool
    {
        try {
            $acquired = $this->lockManager->lock($lockName);
        } catch (\Throwable $e) {
            $this->logger->warning('AI Related All: lock acquisition failed: ' . $e->getMessage());
            $acquired = false;
        }
        if (!$acquired) {
            $output->writeln('Could not acquire lock, another run may be active. Exiting.');
            return false;
        }
        $output->writeln('Lock acquired: ' . $lockName);
        return true;
    }

    /**
     * Run the internal generation loop and return totals as array
     * @return array [processed, saved, skipped]
     */
    private function runLoop(int $batch, bool $force, int $throttleSeconds, OutputInterface $output): array
    {
        $totalProcessed = 0;
        $totalSaved = 0;
        $totalSkipped = 0;
        $iterations = 0;
        try {
            while (true) {
                $iterations++;
                $beforeLast = $this->generator->getLastPersistedId() ?? (int)$this->scopeConfig->getValue('ecommerce66_ai/ai_related/last_entity_id');
                $result = $force ? $this->generator->generateForce($batch) : $this->generator->generate($batch);
                $output->writeln('Iteration ' . $iterations . ': ' . json_encode($result));
                $totalProcessed += $result['processed'];
                $totalSaved += $result['saved'];
                $totalSkipped += $result['skipped'];

                $afterLast = $this->generator->getLastPersistedId() ?? (int)$this->scopeConfig->getValue('ecommerce66_ai/ai_related/last_entity_id');

                if (empty($result['processed']) || $result['processed'] === 0) {
                    break;
                }

                if ($afterLast <= $beforeLast) {
                    $this->logger->warning('AI Related All: no DB progress detected (before=' . $beforeLast . ', after=' . $afterLast . '). Aborting loop to avoid infinite run.');
                    $output->writeln('No DB progress detected (before=' . $beforeLast . ', after=' . $afterLast . '). Aborting.');
                    break;
                }

                // Throttle between batches to avoid API rate limits (configurable via admin)
                if ($throttleSeconds > 0) {
                    sleep($throttleSeconds); // phpcs:ignore Magento2.Functions.DiscouragedFunction
                }
            }
        } catch (\Throwable $e) {
            $this->logger->error('AI Related All: fatal during generate loop: ' . $e->getMessage());
        }
        return [$totalProcessed, $totalSaved, $totalSkipped];
    }

    private function releaseLock(string $lockName): void
    {
        try {
            $this->lockManager->unlock($lockName);
        } catch (\Throwable $e) {
            $this->logger->warning('AI Related All: failed to release lock: ' . $e->getMessage());
        }
    }
}
