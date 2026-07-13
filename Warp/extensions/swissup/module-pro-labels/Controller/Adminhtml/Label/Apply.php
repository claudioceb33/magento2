<?php
namespace Swissup\ProLabels\Controller\Adminhtml\Label;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Json\EncoderInterface;
use Swissup\ProLabels\Model\ResourceModel\Index as ResourceIndex;

class Apply extends \Magento\Backend\App\Action
{
    const PAGE_SIZE = 500;

    private ResultJsonFactory $resultJsonFactory;
    private EncoderInterface $jsonEncoder;
    private ResourceIndex $indexResource;

    public function __construct(
        EncoderInterface $jsonEncoder,
        ResourceIndex $indexResource,
        ResultJsonFactory $resultJsonFactory,
        Context $context
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->indexResource = $indexResource;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Index orders action
     *
     */
    public function execute()
    {
        $indexingLabels = [];
        $labelId = $this->getRequest()->getParam('label_id');
        $session = $this->_session;
        if (!$session->hasData("swissup_labels_init")) {
            if ($labelId) {
                $this->indexResource->cleanLabelIndex($labelId);
                $indexingLabels = [$labelId];
            } else {
                //indexing all labels
                $this->indexResource->cleanIndexes();
                $indexingLabels = $this->indexResource->getAllLabelsIds();
            }

            $session->setData("swissup_labels", $indexingLabels);
            $session->setData("swissup_labels_success", []);
            $session->setData("swissup_label_new", 1);
            $session->setData("swissup_labels_init", 1);
        }

        if ($session->getData("swissup_label_new")) {
            // prepare to reindex new label
            $session->setData("swissup_label_product_count", $this->indexResource->countProducts());
            $session->setData("swissup_label_product_apply", 0);
            $session->setData("swissup_label_step", 0);
            $session->setData("swissup_label_new", 0);

            $percent = 100 * (int)$session->getData("swissup_label_product_apply") / (int)$session->getData("swissup_label_product_count");
            $responseLoaderText = count($session->getData("swissup_labels_success")) + 1
                . ' of ' . count($session->getData("swissup_labels")) . ' - ' . $percent . '%';
            $response = [
                'finished'  => false,
                'loaderText' => $responseLoaderText
            ];
        } else {
            $notApplyedLabelIds = array_diff(
                $session->getData("swissup_labels"),
                $session->getData("swissup_labels_success")
            );
            $labelId = reset($notApplyedLabelIds);
            $productsForIndexing = $this->indexResource->getProductIds(
                self::PAGE_SIZE,
                $session->getData("swissup_label_step")
            );
            if (count($productsForIndexing) > 0) {
                $productCountForIndexing = count($productsForIndexing);
                $reindexedProductCount = $productCountForIndexing + (int)$session->getData("swissup_label_product_apply");
                $session->setData("swissup_label_product_apply", $reindexedProductCount);
                $this->indexResource->buildIndexes($productsForIndexing, [$labelId]);
                $prevStep = (int)$session->getData("swissup_label_step");
                $nextStep = $prevStep + 1;
                $session->setData("swissup_label_step", $nextStep);

                $percent = 100 * (int)$session->getData("swissup_label_product_apply") / (int)$session->getData("swissup_label_product_count");
                $responseLoaderText = count($session->getData("swissup_labels_success")) + 1
                    . ' of ' . count($session->getData("swissup_labels")) . ' - ' . (int)$percent . '%';

                $response = [
                    'finished'  => false,
                    'loaderText' => $responseLoaderText
                ];
            } else {
                // finish aplly label
                $percent = 100 * (int)$session->getData("swissup_label_product_apply") / (int)$session->getData("swissup_label_product_count");
                $responseLoaderText = count($session->getData("swissup_labels_success")) + 1
                    . ' of ' . count($session->getData("swissup_labels")) . ' - ' . (int)$percent . '%';
                $successLabels = $session->getData("swissup_labels_success");
                $successLabels[] = $labelId ;
                $session->setData("swissup_labels_success", $successLabels);
                $notApplyedLabelIds = array_diff(
                    $session->getData("swissup_labels"),
                    $session->getData("swissup_labels_success")
                );
                if (count($notApplyedLabelIds) > 0) {
                    $session->setData("swissup_label_new", 1);
                    $response = [
                        'finished'  => false,
                        'loaderText' => $responseLoaderText
                    ];
                } else {
                    //all labels are applyed
                    $successCount = count($session->getData("swissup_labels_success"));
                    $session->unsetData("swissup_labels_init");
                    $session->unsetData("swissup_label_product_apply");
                    $session->unsetData("swissup_labels");
                    $session->unsetData("swissup_label_product_count");
                    $session->unsetData("swissup_labels_success");
                    $session->unsetData("swissup_label_step");
                    if ($successCount > 1) {
                        $this->messageManager->addSuccess(__('Labels have been applied.'));
                    } else {
                        $this->messageManager->addSuccess(__('Label has been applied.'));
                    }

                    $response = [
                        'finished'  => true
                    ];
                }
            }
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData($response);
    }
}
