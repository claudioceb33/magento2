<?php
namespace Ceb\InvoiceA\Controller\Quote;

use Magento\Framework\Exception\NoSuchEntityException;

class Save extends \Magento\Framework\App\Action\Action
{
    protected $quoteIdMaskFactory;

    protected $quoteRepository;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        parent::__construct($context);
        $this->quoteRepository = $quoteRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        if ($post) {
            $cartId = $post['cartId'] ?? '';
            $customerTaxSituation = $post['customerTaxSituation'] ?? '';
            $customerCuit = $post['customerCuit'] ?? '';
            $customerCompany = $post['customerCompany'] ?? '';
            $loggin = $post['is_customer'] ?? '';

            if ($loggin === 'false') {
                $cartId = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id')->getQuoteId();
            }

            $quote = $this->quoteRepository->getActive($cartId);
            if (!$quote->getItemsCount()) {
                throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
            }

            $quote->setData('customer_tax_situation', $customerTaxSituation);
            $quote->setData('customer_cuit', $customerCuit);
            $quote->setData('customer_company', $customerCompany);
            $this->quoteRepository->save($quote);
        }
    }
}
