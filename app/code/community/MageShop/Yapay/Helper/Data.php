<?php 

class MageShop_Yapay_Helper_Data extends Mage_Core_Helper_Abstract
{
    const MS_YAPAY_TOKEN = "payment/mageshop_yapay_custompayment/auth_token";
    const MS_YAPAY_KEY_ENV = "payment/mageshop_yapay_custompayment/environment";
    const MS_YAPAY_BASE_URL = "payment/mageshop_yapay_custompayment/base_url";
    const MS_YAPAY_AVAILABLE_PAYMENT_METHOD = "payment/yapay_creditcardpayment/available_payment_methods";
    const MS_YAPAY_COUNT_MAX_SPLIT = "payment/yapay_creditcardpayment/installments";
    const MS_YAPAY_COUNT_MIN_SPLIT = "payment/yapay_creditcardpayment/min_installment";
    const MS_YAPAY_SANDBOX = "https://api.intermediador.sandbox.yapay.com.br/";
    const MS_YAPAY_API = "https://api.intermediador.yapay.com.br/";
    const MS_YAPAY_CPF_FORM_CC_ENABLE = "payment/yapay_creditcardpayment/capture_tax";

    public function getCountMaxSprit()
    {
        return (int) Mage::getStoreConfig(self::MS_YAPAY_COUNT_MAX_SPLIT);
    }
    public function getCountMinSprit()
    {
        return (int) Mage::getStoreConfig(self::MS_YAPAY_COUNT_MIN_SPLIT);
    }
    public function getToken()
    {
        return Mage::getStoreConfig(self::MS_YAPAY_TOKEN);
    }
    public function getKeyKonduto()
    {
        return Mage::getStoreConfig(self::MS_YAPAY_KEY_KONDUTO);
    }
    public function getEnvironment(){
        return Mage::getStoreConfig(self::MS_YAPAY_KEY_ENV);
    }

    public function getCaptureTaxCc(){
        return Mage::getStoreConfig(self::MS_YAPAY_CPF_FORM_CC_ENABLE);
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
}
