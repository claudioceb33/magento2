<?php

namespace Swissup\AddressFieldManager\Controller\Address;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Edit extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\AddressRegistry
     */
    private $addressRegistry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @param \Magento\Customer\Model\AddressRegistry $addressRegistry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Customer\Model\AddressRegistry $addressRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->addressRegistry = $addressRegistry;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $id = $this->_request->getParam('id');

        if (!$id) {
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('/');
        }

        try {
            $address = $this->addressRegistry->retrieve($id);
            if ($this->customerSession->getCustomerId() != $address->getCustomerId()) {
                throw NoSuchEntityException::singleField('addressId', $id);
            }
        } catch (\Exception $e) {
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('/');
        }

        $errors = $address->validate();
        if (is_array($errors)) {
            foreach ($errors as $error) {
                $this->messageManager->addError($error);
            }
        }

        return $this->resultFactory
            ->create(ResultFactory::TYPE_REDIRECT)
            ->setPath('customer/address/edit', [
                'id' => $id,
            ]);
    }
}
