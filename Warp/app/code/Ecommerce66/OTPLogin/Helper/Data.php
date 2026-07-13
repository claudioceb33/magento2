<?php
namespace Ecommerce66\OTPLogin\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    protected const CONFIG_PATH_GENERAL_ACTIVE = 'otpcode/general/active';
    protected const CONFIG_PATH_GENERAL_OTP_CODE_LENGTH = 'otpcode/general/otp_code_length';
    protected const CONFIG_PATH_GENERAL_OTP_REQUEST_TIME = 'otpcode/general/otp_code_request_time';
    protected const CONFIG_PATH_GENERAL_EMAIL_MESSAGE_SENT ='otpcode/messages/email_message_sent';
    protected const CONFIG_PATH_GENERAL_EMAIL_MESSAGE_ERROR ='otpcode/messages/email_message_error';
    protected const CONFIG_PATH_GENERAL_OTP_MESSAGE_ERROR ='otpcode/messages/otp_message_error';

    /**
     * Check if OTP Integration is enabled.
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_ACTIVE);
    }

    /**
     * Get OTP Code Length from configuration.
     *
     * @return int
     */
    public function getOtpCodeLength()
    {
        return (int)$this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_OTP_CODE_LENGTH);
    }

    /**
     * Get OTP Code Min time between requests.
     *
     * @return int
     */
    public function getOtpRequestTime()
    {
        return (int)$this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_OTP_REQUEST_TIME) ?? 120;
    }

    /**
     * @return mixed
     */
    public function getEmailMessage()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_EMAIL_MESSAGE_SENT);
    }

    /**
     * @return mixed
     */
    public function getEmailMessageError()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_EMAIL_MESSAGE_ERROR);
    }

    /**
     * @return mixed
     */
    public function getErrorMessage()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL_OTP_MESSAGE_ERROR);
    }

    public function getEmailName()
    {
        return $this->scopeConfig->getValue('trans_email/ident_general/name');
    }

    /**
     * @return mixed
     */
    public function getEmailAddress()
    {
        return $this->scopeConfig->getValue('trans_email/ident_general/email');
    }
}
