<?php 

class MageShop_Yapay_Model_Payment_Cc_Request{
    private $_helper;
    private $_curl;
    private $_data;
    const URI_CC = "api/v3/transactions/payment";
    public function __construct()
    {
        $this->_helper = Mage::helper("mageshop_yapay/data");
        $this->_data = $this->ccData();
        $this->_curl = new MageShop_Yapay_Service_Rest;
    }
    /**
     * Gera o pedido - Api yapay
     *
     * @param object|Varien_Object $payment
     * @param $amount
     * @param object $info
     * @return array
     */
    public function transitionCc(Varien_Object $payment, $amount, $info)
    {
        $_url =  $this->_helper->getUrlEnvironment() . self::URI_CC;
        $pushData = $this->_data->getDataCc($payment, $info);
        $raw = json_encode($pushData, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        $this->_curl->url($_url)->_method('POST')->_body($raw)->exec();
        $response = $this->_curl->getResponse();
        $log = array("URL" => $_url, "POSTFIELDS" => $raw, "RESPONSE" => $response);
        $this->_curl->setLogYapay("RESPONSE", var_export($log, true), "mageshop_yapay_cc_request.log");
        if(empty($response) || $response == null){
            Mage::throwException($this->_helper->__("Algo não ocorreu bem. Por favor verifique suas informações ou altere a forma de pagamento."));
        }
        return json_decode($response, true);
    }
    public function ccData()
    {
        return new MageShop_Yapay_Model_Payment_Cc_Raw;
    }
}