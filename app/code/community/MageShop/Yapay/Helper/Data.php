<?php 

class MageShop_Yapay_Helper_Data extends Mage_Core_Helper_Abstract
{
    const MS_YAPAY_TOKEN = "payment/mageshop_yapay_custompayment/auth_token";
    const MS_YAPAY_RESELLER_TOKEN = "sales/mageshop_yapay/reseller_token";
    const MS_YAPAY_KEY_ENV = "payment/mageshop_yapay_custompayment/environment";
    const MS_YAPAY_BASE_URL = "payment/mageshop_yapay_custompayment/base_url";
    const MS_YAPAY_AVAILABLE_PAYMENT_METHOD = "payment/yapay_creditcardpayment/available_payment_methods";
    const MS_YAPAY_COUNT_MAX_SPLIT = "payment/yapay_creditcardpayment/installments";
    const MS_YAPAY_COUNT_MIN_SPLIT = "payment/yapay_creditcardpayment/min_installment";
    const MS_YAPAY_SANDBOX = "https://api.intermediador.sandbox.yapay.com.br/";
    const MS_YAPAY_API = "https://api.intermediador.yapay.com.br/";
    const MS_YAPAY_CPF_FORM_CC_ENABLE = "payment/yapay_creditcardpayment/capture_tax";
    const MS_YAPAY_CPF_FORM_PIX_ENABLE = "payment/yapay_pix/capture_tax";
    const MS_YAPAY_CPF_FORM_BANKSLIP_ENABLE = "payment/yapay_bankslip/capture_tax";
    const MS_YAPAY_CPF_FORM_CC_INTEREST_ACTIVE = "payment/yapay_creditcardpayment/interest_active";
    const MS_YAPAY_CPF_FORM_CC_INSTALLMENT_INTEREST = "payment/yapay_creditcardpayment/installment_interest";

    public function getCountMaxSplit()
    {
        return (int) Mage::getStoreConfig(self::MS_YAPAY_COUNT_MAX_SPLIT);
    }
    public function getCountMinSplit()
    {
        return (int) Mage::getStoreConfig(self::MS_YAPAY_COUNT_MIN_SPLIT);
    }
    public function getToken()
    {
        return Mage::getStoreConfig(self::MS_YAPAY_TOKEN);
    }
    public function getResellerToken()
    {
        return Mage::getStoreConfig(self::MS_YAPAY_RESELLER_TOKEN);
    }
    public function getEnvironment(){
        return Mage::getStoreConfig(self::MS_YAPAY_KEY_ENV);
    }
    public function getCaptureTaxCc(){
        return Mage::getStoreConfig(self::MS_YAPAY_CPF_FORM_CC_ENABLE);
    }

    public function getCaptureTaxBankslip(){
        return Mage::getStoreConfig(self::MS_YAPAY_CPF_FORM_BANKSLIP_ENABLE);
    }

    public function getCaptureTaxPix(){
        return Mage::getStoreConfig(self::MS_YAPAY_CPF_FORM_PIX_ENABLE);
    }
    public function getEnvironmentFingerPrint()
    {
        if ($this->getEnvironment() == 'sandbox') {
            return "env: 'sandbox'";
        } else {
            return '';
        }
    }
    public function getUrlEnvironment()
    {
        if ($this->getEnvironment() == 'sandbox') {
            return self::MS_YAPAY_SANDBOX;
        } else {
            return self::MS_YAPAY_API;
        }
    }
    public function getBaseUrl()
    {
        return Mage::getStoreConfig(trim( self::MS_YAPAY_BASE_URL , '/' ));
    }

    public function getAvailablePaymentMethodsCc()
    {
        return Mage::getStoreConfig( self::MS_YAPAY_AVAILABLE_PAYMENT_METHOD );
    }
    public function monetize($value)
    {
        if (empty($value)) {
            return 0.00;
        }
        if (is_float($value)) {
            return (float) number_format($value, 2, '.', '');
        }
        if (is_string($value) && strpos($value, ',') !== false) {
            $value = str_replace(',', '.', $value);
        }
        $value = floor($value * 100) / 100;
        $value = (float) number_format($value, 2, '.', '');
        return $value;
    }

}
