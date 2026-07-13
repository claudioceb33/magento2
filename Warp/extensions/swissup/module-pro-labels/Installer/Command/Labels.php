<?php

namespace Swissup\ProLabels\Installer\Command;

class Labels
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Swissup\ProLabels\Model\LabelFactory
     */
    private $labelFactory;

    /**
     * @var \Swissup\ProLabels\Model\IndexerFactory
     */
    private $labelsIndexerFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory
     */
    private $customerGroupsCollectionFactory;

    /**
     * @param \Swissup\ProLabels\Model\LabelFactory $labelFactory
     * @param \Swissup\ProLabels\Model\IndexerFactory $labelsIndexerFactory
     * @param \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupsCollectionFactory
     */
    public function __construct(
        \Swissup\ProLabels\Model\LabelFactory $labelFactory,
        \Swissup\ProLabels\Model\IndexerFactory $labelsIndexerFactory,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupsCollectionFactory
    ) {
        $this->labelFactory = $labelFactory;
        $this->labelsIndexerFactory = $labelsIndexerFactory;
        $this->customerGroupsCollectionFactory = $customerGroupsCollectionFactory;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * Create new labels.
     * If duplicate is found - do nothing.
     *
     * @param \Swissup\Marketplace\Installer\Request $request
     */
    public function execute($request)
    {
        $this->logger->info('ProLabels: Create labels');

        $storeIds = $request->getStoreIds();
        $storeIds = array_map('strval', $storeIds);
        $groupIds = $this->getCustomerGroupsIds();

        foreach ($request->getParams() as $data) {
            $label = $this->labelFactory
                ->create()
                ->load($data['title'], 'title');

            if ($label->getId()) {
                continue;
            }

            $data = array_merge([
                'status' => 1
            ], $data);

            try {
                $label->setData($data)
                    ->setStoreId($storeIds)
                    ->setCustomerGroups($groupIds)
                    ->save();
            } catch (\Exception $e) {
                $this->logger->warning($e->getMessage());
            }
        }

        // apply labels
        $this->logger->info('ProLabels: Indexing labels');
        $this->labelsIndexerFactory->create()->executeFull();
    }

    /**
     * Get customer groups ids
     *
     * @return array
     */
    protected function getCustomerGroupsIds()
    {
        $customerGroups = $this->customerGroupsCollectionFactory->create()
            ->toOptionArray();

        foreach($customerGroups as $group) {
            $groupIds[] = $group['value'];
        }

        return $groupIds;
    }
}
