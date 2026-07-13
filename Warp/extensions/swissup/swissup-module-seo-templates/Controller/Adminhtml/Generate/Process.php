<?php

namespace Swissup\SeoTemplates\Controller\Adminhtml\Generate;

class Process extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_SeoTemplates::template_generate';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Swissup\SeoTemplates\Model\Generator
     */
    protected $generator;

    /**
     * @var Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Swissup\SeoTemplates\Model\Generator $generator,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->generator = $generator;
        $this->localeDate = $localeDate;
    }

    /**
     * Process action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $pageSize = $this->getRequest()->getParam('page_size', 33);
        $curPage = $this->getRequest()->getParam('cur_page', 0);
        $entityType = explode(',', $this->getRequest()->getParam('entity_type'));
        $this->generator
            ->setPageSize($pageSize)
            ->setCurPage($curPage)
            ->setEntityType($entityType[0])
            ->generate();

        if ($this->generator->getNextPage()) {
            $nextPage = $this->generator->getNextPage();
        } else {
            array_shift($entityType);
            $nextPage = 1;
        }

        $response = ['log' => $this->prepareLogResponse()];
        if (isset($entityType[0])) {
            // process does not complete
            $response['url'] = $this->_url->getUrl('*/*/*');
            $response['page_size'] = $pageSize;
            $response['next_page'] = $nextPage;
            $response['entity_type'] = implode(',', $entityType);
        } else {
            // process complete
            $currentDateTime = $this->localeDate->formatDate(
                null,
                \IntlDateFormatter::MEDIUM,
                true
            );
            $response['log'][] = [
                'lineId' => 'row-end',
                'text' => __('Complete at %1', $currentDateTime)
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }

    /**
     * Preapre log response
     *
     * @return array
     */
    private function prepareLogResponse()
    {
        if ($this->generator->getCurPage() > 0) {
            $total = $this->generator->getPageSize() * ($this->generator->getCurPage() - 1)
                + $this->generator->getProcessedItems();
        } else {
            $total = 0;
        }

        $type = $this->generator->getEntityType();
        $typeName = $this->generator->getEntityTypeName($type);

        return [
            [
                'lineId' => 'row-' . $type,
                'text' => __('%1: items processed - %2', $typeName, $total)
            ]
        ];
    }
}
