<?php
class MageShop_Yapay_Block_Info_Creditcard extends MageShop_Yapay_Block_Payment_Status
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mageshop/yapay/payment/info/creditcard.phtml');
    }
    
    public function _getFlag($payment_method_id)
    {
        switch ((int) $payment_method_id) {
            case '3':  return "visa";
            case '4':  return "mastercard";
            case '5':  return "american_express";
            case '15': return "discover";
            case '16': return "elo";
            case '18': return "aura";
            case '19': return "jcb";
            case '20': return "hipercard";
            case '25': return "hiper";
        }
    }
}