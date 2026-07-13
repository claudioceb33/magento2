<?php

namespace Ecommerce66\Core\Console\Command;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class SetNoFollow extends Command
{
    const COMMAND_NAME = 'catalog:product:attributes:nofollow';

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * SetNoFollow constructor.
     *
     * @param AttributeRepositoryInterface $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param string|null $name
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        string $name = null
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($name);
    }

    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Set "nofollow" option to "yes" for product attributes that are filterable in layered navigation');

        parent::configure();
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            // Build search criteria
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('main_table.entity_type_id', 4) // 4 is for catalog product
                ->addFilter('additional_table.is_filterable', ['eq' => 1]) // Filterable attributes
                ->create();

            // Get the list of attributes
            $attributes = $this->attributeRepository->getList('catalog_product', $searchCriteria);

            $attributesUpdated = 0;

            foreach ($attributes->getItems() as $attribute) {
                echo '    '.$attribute->getAttributeCode()."\n"; // phpcs:ignore
                // Update attribute
                $attribute->setData('is_display_rel_nofollow', 1); // Update the attribute with 'nofollow'
                $this->attributeRepository->save($attribute);
                $attributesUpdated++;
            }

            $output->writeln("<info>Successfully updated {$attributesUpdated} attributes to 'nofollow' = 'yes'</info>");
        } catch (LocalizedException $e) {
            $output->writeln("<error>LocalizedException: {$e->getMessage()}</error>");
            return Cli::RETURN_FAILURE;
        } catch (NoSuchEntityException $e) {
            $output->writeln("<error>NoSuchEntityException: {$e->getMessage()}</error>");
            return Cli::RETURN_FAILURE;
        } catch (\Exception $e) {
            $output->writeln("<error>Exception: {$e->getMessage()}</error>");
            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }
}
