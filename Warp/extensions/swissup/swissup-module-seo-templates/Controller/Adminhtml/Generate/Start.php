<?php

namespace Swissup\SeoTemplates\Controller\Adminhtml\Generate;

class Start extends Process
{
    /**
     * Start action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $entityType = explode(',', $this->getRequest()->getParam('entity_type'));
        $response = [
            'log' => $this->prepareLogResponse(),
            'page_size' => $this->getRequest()->getParam('page_size', 33),
            'next_page' => 1,
            'entity_type' => implode(',', $entityType),
            'url' => $this->_url->getUrl('*/*/process')
        ];
        // clear templates log
        $this->generator->claerTemplatesLogs($entityType);
        $this->generator->clearGeneratedData($entityType);
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
        $currentDateTime = $this->localeDate->formatDate(
            null,
            \IntlDateFormatter::MEDIUM,
            true
        );
        return [
            [
                'lineId' => 'row-start',
                'text' => __('Start at %1', $currentDateTime)
            ]
        ];
    }
}
