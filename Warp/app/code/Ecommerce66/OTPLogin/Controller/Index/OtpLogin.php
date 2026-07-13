<?php

declare(strict_types=1);

namespace Ecommerce66\OTPLogin\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Ecommerce66\OTPLogin\Model\OtpCode;
use Ecommerce66\OTPLogin\Helper\Data;

class OtpLogin extends Action
{
    /**
     * @var JsonFactory
     */
    protected JsonFactory $resultJsonFactory;

    /**
     * @var OtpCode
     */
    protected OtpCode $otpCode;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected \Magento\Framework\Session\SessionManagerInterface $sessionManager;

    /**
     * @var Data
     */
    protected Data $helper;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param OtpCode $otpCode
     * @param \Magento\Framework\Session\SessionManagerInterface $sessionManager
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        OtpCode $otpCode,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        Data $helper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->otpCode = $otpCode;
        $this->sessionManager = $sessionManager;
        $this->helper = $helper;
    }

    /**
     * @return Json
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(): Json
    {
        if (!$this->getRequest()->isPost() || !$this->getRequest()->isXmlHttpRequest() || !$this->helper->isActive()) {
            $result = $this->resultJsonFactory->create();
            return $result->setData([
                'error' => true,
                'message' => __('Solicitud no permitida')
            ]);
        }

        $result = $this->resultJsonFactory->create();

        $email = $this->getRequest()->getParam('email');
        $userCode = $this->getRequest()->getParam('otpcode');

        if ($email && !$this->isValidEmail($email)) {
            return $result->setData([
                'type' => 'error',
                'message' => __('Correo electrónico no válido')
            ]);
        }

        if ($userCode && !$this->isValidCode($userCode)) {
            return $result->setData([
                'type' => 'error',
                'message' => __('Código no numérico')
            ]);
        }

        if ($email) {
            $lastEmailRequestTime = $this->sessionManager->getLastEmailRequestTime();
            $currentTime = time();

            if ($lastEmailRequestTime !== null) {
                $timeSinceLastEmailRequest = $currentTime - $lastEmailRequestTime;
                $timeEmailRequests = $this->helper->getOtpRequestTime();

                if ($timeSinceLastEmailRequest < $timeEmailRequests) {
                    $timeRemaining = $timeEmailRequests - $timeSinceLastEmailRequest;
                    return $result->setData([
                        'type' => 'notice',
                        'message' => __('Debes esperar %1 segundos antes de solicitar un nuevo código', $timeRemaining)
                    ]);
                }
            }

            $this->sessionManager->setLastEmailRequestTime($currentTime);
            return $this->handleEmailRequest($result, $email);
        }

        return $this->handleUserCode($result, $userCode);
    }

    /**
     * @param string|null $email
     * @return bool
     */
    protected function isValidEmail(?string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @param string|null $code
     * @return bool
     */
    protected function isValidCode(?string $code): bool
    {
        return ctype_digit((string)$code);
    }

    /**
     * @param Json $result
     * @param string $email
     * @return Json
     */
    protected function handleEmailRequest(Json $result, string $email): Json
    {
        $this->sessionManager->setEmailRequest($email);

        try {
            $generatedCode = $this->otpCode->generateCodeAndSendByEmail($email);
            $this->sessionManager->setOtpRequest((int)$generatedCode['code']);

            return $result->setData([
                'type' => 'success',
                'message' => $this->helper->getEmailMessage()
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'type' => 'error',
                'message' => $this->helper->getEmailMessageError()
            ]);
        }
    }

    /**
     * @param Json $result
     * @param string $userCode
     * @return Json
     */
    protected function handleUserCode(Json $result, string $userCode): Json
    {
        $storedOtpRequest = $this->sessionManager->getOtpRequest();

        if ((int)$userCode === $storedOtpRequest) {
            $this->otpCode->setCustomerLoggedIn($this->sessionManager->getEmailRequest());

            return $result->setData([
                'type' => 'success',
                'message' => __('El código es correcto'),
                'reload_page' => true
            ]);
        }

        return $result->setData([
            'type' => 'error',
            'message' => $this->helper->getErrorMessage()
        ]);
    }
}
