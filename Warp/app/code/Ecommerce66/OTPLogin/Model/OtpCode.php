<?php
namespace Ecommerce66\OTPLogin\Model;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Ecommerce66\OTPLogin\Helper\Data as OtpHelper;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\CustomerFactory;

class OtpCode
{
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Random
     */
    protected $random;

    /**
     * @var OtpHelper
     */
    protected $otpHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param CustomerRepositoryInterface $customerRepository
     * @param Random $random
     * @param OtpHelper $otpHelper
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        CustomerRepositoryInterface $customerRepository,
        Random $random,
        OtpHelper $otpHelper,
        StoreManagerInterface $storeManager,
        Session $customerSession,
        CustomerFactory $customerFactory
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->customerRepository = $customerRepository;
        $this->random = $random;
        $this->otpHelper = $otpHelper;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
    }

    /**
     * Generate a random code for OTP.
     * @return string
     */
    public function generateCode()
    {
        $codeLength = $this->otpHelper->getOtpCodeLength();
        $min = pow(10, $codeLength - 1);
        $max = pow(10, $codeLength) - 1;

        return $this->random->getRandomNumber($min, $max);
    }

    /**
     * Generate code, save it to the customer entity, and send it via email.
     * @param string $email
     * @return array Generated code
     * @throws LocalizedException
     */
    public function generateCodeAndSendByEmail($email)
    {
        try {
            $customer = $this->customerRepository->get($email);
            if ($customer->getId()) {
                $code = $this->generateCode();
                $this->sendEmail($email, $code);

                return ['email' => $email, 'code' => $code];
            }
        } catch (\Exception $e){
            return false;
        }
    }

    /**
     * Verify the provided code against the stored code for a given email.
     * @param string $email
     * @return bool
     */
    public function setCustomerLoggedIn($email)
    {
        $customerData = $this->customerRepository->get($email);
        $customer = $this->customerFactory->create()->load($customerData->getId());
        $this->customerSession->setCustomerAsLoggedIn($customer);
    }

    /**
     * Send the OTP code via email.
     * @param string $email
     * @param string $code
     * @throws LocalizedException
     */
    protected function sendEmail($email, $code)
    {
        $this->inlineTranslation->suspend();

        $store = $this->storeManager->getStore();
        $storeId = $store->getId();
        $storeName = $store->getFrontendName();

        $from = [
            'email' => $this->otpHelper->getEmailAddress(),
            'name' => $this->otpHelper->getEmailName()
        ];

        $transport = $this->transportBuilder
            ->setTemplateIdentifier('e66_otp_code_email_template')
            ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId]
            )->setFrom($from)
            ->addTo($email)
            ->setTemplateVars(['code' => $code,'store_name' => $storeName])
            ->getTransport();

        $transport->sendMessage();

        $this->inlineTranslation->resume();
    }
}
