<?php
namespace Ecommerce66\AiRelated\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;
use Ecommerce66\AiRelated\Model\Generator;
use Ecommerce66\AiRelated\Helper\Data as AiHelper;

class GenerateRelated extends Command
{
    protected static $defaultName = 'ecommerce66:ai:generate-related';
    private $generator;
    private $logger;
    private $aiHelper;

    public function __construct(Generator $generator, LoggerInterface $logger, AiHelper $aiHelper)
    {
        $this->generator = $generator;
        $this->logger = $logger;
        $this->aiHelper = $aiHelper;
        // Ensure command name is set so generated interceptors don't have an empty name
        parent::__construct(self::$defaultName);
    }

    protected function configure()
    {
        $this->setDescription('Generate AI related products in batch')
            ->addOption('batch', null, InputOption::VALUE_OPTIONAL, 'Batch size', 50)
            ->addOption('start-id', null, InputOption::VALUE_OPTIONAL, 'Start entity_id (overrides saved progress)', null)
            ->addOption('reset', null, InputOption::VALUE_NONE, 'Reset saved progress and start from beginning')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force override existing related links');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $batch = (int)$input->getOption('batch');
        $output->writeln('Starting AI related generation with batch ' . $batch);
        $startId = $input->getOption('start-id') !== null ? (int)$input->getOption('start-id') : null;
        $reset = (bool)$input->getOption('reset');
        if ($reset) {
            $this->generator->resetProgress();
            $output->writeln('Progress reset.');
        }
        $force = (bool)$input->getOption('force');
        $result = $force ? $this->generator->generateForce($batch, $startId) : $this->generator->generate($batch, $startId);
        $output->writeln('Finished: ' . json_encode($result));
        if ($this->aiHelper->isInfoLoggingEnabled()) {
            $this->logger->info('AI Related CLI: ' . json_encode($result));
        }
        return Command::SUCCESS;
    }
}
