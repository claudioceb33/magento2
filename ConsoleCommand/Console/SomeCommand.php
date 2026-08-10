<?php

declare(strict_types=1);

namespace Ceb\ConsoleCommand\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SomeCommand extends Command
{
    private const NAME = 'name';

    public function __construct(
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('my:first:command');
        $this->setDescription('This is my first console command.');
        $this->addOption(
            self::NAME,
            null,
            InputOption::VALUE_REQUIRED,
            'Name'
        );

        parent::configure();
    }

    /**
     * Execute the command -> php bin/magento my:first:command --name 'John'
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
     protected function execute(InputInterface $input, OutputInterface $output): int
     {
         $exitCode = 0;
         
         if ($name = $input->getOption(self::NAME)) {
             $output->writeln('<info>Provided name is `' . $name . '`</info>');
         }

         $output->writeln('<info>Success message</info>');
         $output->writeln('<comment>Some comment</comment>');

         try {
             if (rand(0, 1)) {
                throw new LocalizedException(__('An error occurred.'));
             }
         } catch (LocalizedException $e) {
             $output->writeln(sprintf(
                 '<error>%s</error>',
                 $e->getMessage()
             ));
             $exitCode = 1;
         }
         
         return $exitCode;
    }

    /**
     * Execute command: php bin/magento customshipping:shipment:shiporder
     * 
     * @param InputInterface $input
     * @param OutputInterface $output
     * 
     */
    protected function configureTwo()
    {
        $this->setName('customshipping:shipment:shiporder')
            ->setDescription('generarte shipments.')
            ->setDefinition([]);
        parent::configure();
    }

    protected function executeTwo(InputInterface $input, OutputInterface $output)
    {
        $this->_appState->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_GLOBAL,
            [$this, 'createShipment'],
            [$input, $output]
        );
    }

    public function createShipment(InputInterface $input, OutputInterface $output)
    {
        try {
            $output->writeln('Init createShipment()');
            /*..Procedure..*/
        }
        catch(\Exception $ex) {
            $output->writeln('Hubo un error al ejecutar el proceso: ' . $ex->getMessage());
        }
    }
}
