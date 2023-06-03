<?php

/**
 * Yapay Transparente Magento
 * Yapay Credit Card Model Class - Proccess Payment Cc
 *
 * @category    MageShop
 * @package     MageShop_Yapay
 * @author      Vitor Costa
 * @copyright   Copyright (c)  2018 mageshop (https://www.mageshop.com.br/ | https://github.com/csvitor/MageShop_Yapay)
 */
class MageShop_Yapay_Model_Payment_Method_Cc extends MageShop_Yapay_Model_Payment_Abstract
{
    const PAY_CODE = 'yapay_creditcardpayment';
    const URI_TRANSACTION = "api/v3/transactions/payment";
    protected $_code = self::PAY_CODE;
    protected $_formBlockType = 'mageshop_yapay/form_creditcard';
    protected $_infoBlockType = 'mageshop_yapay/info_creditcard';
    protected $_canOrder = true;
    protected $_isInitializeNeeded = true;
    protected $_isGateway = true;
    protected $_allowCurrencyCode = ["BRL"];
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;
    protected $_canSaveCc = false;


    /**
     * Method that will be executed instead of magento's authorize default
     * workflow
     *
     * @param string $paymentAction
     * @param Varien_Object $stateObject
     *
     * @return Mage_Payment_Model_Method_Abstract
     */
    public function initialize($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
        $this->authorize($payment, $payment->getOrder()->getBaseTotalDue());
    }
    /**
     * Processa o pedido
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function authorize(Varien_Object $payment, $amount)
    {
      if ($this->canOrder()) {
        $resYapay = $this->transactionYapay($payment, $amount, $this->getInfoInstance());
        $validity = $this->_validity($resYapay);
        if($validity['general_errors']){
            Mage::throwException(Mage::helper("mageshop_yapay/data")->__($validity['error']));
            return $this;
        }
        $payment->setAdditionalInformation("transaction_id", $validity['transaction_id']);
        $payment->setAdditionalInformation("status_id", $validity['status_id']);
        $payment->setAdditionalInformation("token_transaction", $validity['token_transaction']);
        $payment->setAdditionalInformation("status_name", $validity['status_name']);
        $payment->setAdditionalInformation("payment",  $validity['payment']);
        $payment->setAdditionalInformation("transaction",  $validity['transaction']);
        return $this;
      }
    }

    /**
     * Cancela o pagamento
     *
     * @param Varien_Object $payment
     * @return Mage_Payment_Model_Abstract
     */
    public function cancel(Varien_Object $payment)
    {
        // Aqui é onde você cancela o pagamento
        if (!$payment->canCancel()) {
            return true;
        }
        return $this;
    }
    
     /**
     *  Essa função é responsável por armazenar as informações do pagamento recebidas do formulário de checkout.
     *  Ela recebe um objeto Varien contendo os dados do formulário e os atribui à instância de informação do pagamento.
     *  Em seguida, realiza uma validação dos dados e armazena-os em "additional_information" no formato de array.
     *
     * @param Varien_Object $data
     * @return void
     */
    public function assignData(Varien_Object $data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        $info->setCheckNo($data->getCheckNo())->setCheckDate($data->getCheckDate());

        $info->getQuote()->setYapayDiscount(0.0);
        $info->getQuote()->setYapayInterest(0.0);
        $info->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
        $split_number = @$data->getYapayCreditcardpaymentCcSplitNumber();
        if(!empty($split_number)){
           $info->getQuote()->setYapayCcSplitNumber($split_number);
        }
        $_interest_helper = Mage::helper("mageshop_yapay/interest");
        if($_interest_helper->applyInterest($split_number)){
            $_interest_helper->setInterestCc($info);
        }
        $_discount_helper = Mage::helper("mageshop_yapay/discount");
        if($_discount_helper->getDiscountActiveCreditCard()){
            $_discount_helper->setDiscountCc($info);
        }
        $dataAssign = $this->saveCcAssignData($data);
        $info->setAdditionalInformation("data", $dataAssign);
        $info->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
        return $this;
    }
    /**
     * Function to save assign data
     * @var array|mixed
     * @return string
     */
    public function saveCcAssignData($data)
    {
        $array = [
            'method' => $data['method'],
            'yapay_creditcardpayment_cc_number'             => $data['yapay_creditcardpayment_cc_number'],
            'yapay_creditcardpayment_cc_cid'                => $data['yapay_creditcardpayment_cc_cid'],
            'yapay_creditcardpayment_cc_name'               => $data['yapay_creditcardpayment_cc_name'],
            'yapay_creditcardpayment_cc_expdate_month'      => $data['yapay_creditcardpayment_expiration_month'],
            'yapay_creditcardpayment_cc_expdate_year'       => $data['yapay_creditcardpayment_expiration_yr'],
            'yapay_creditcardpayment_cc_split_number'       => $data['yapay_creditcardpayment_cc_split_number'],
            'yapay_creditcardpayment_cc_split_number_value' => $data['yapay_creditcardpayment_cc_split_number_value'],
            'yapay_creditcardpayment_cc_document'           => $data['yapay_creditcardpayment_cc_document'],
            'yapay_creditcardpayment_cc_finger_print'       => $data['yapay_creditcardpayment_cc_finger_print'],
            'yapay_form_cpf'                => isset($data['yapay_creditcardpayment_cc_cpf']) ? $data['yapay_creditcardpayment_cc_cpf'] : null,
        ];
        return json_encode(array_filter($array));
    }

    /**
     * Gera o pedido - Api yapay
     *
     * @param object|Varien_Object $payment
     * @param $amount
     * @param object $info
     * @return array
     */
    public function transactionYapay(Varien_Object $payment, $amount, $info)
    {
        $_helper = Mage::helper("mageshop_yapay/data");
        $_data = $this->ccData();
        $_curl = new MageShop_Yapay_Service_Rest;
        $_url =  $_helper->getUrlEnvironment() . self::URI_TRANSACTION;
        $pushData = $_data->getDataCc($payment, $info);
        $raw = json_encode($pushData, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        // grava o pedido no yapay
        $_curl->url($_url)->_method('POST')->_body($raw)->exec();
        // resultado yapay
        $response = $_curl->getResponse();
        $log = array("URL" => $_url, "POSTFIELDS" => $raw, "RESPONSE" => $response);
        // grava o log
        $_curl->setLogYapay("RESPONSE", var_export($log, true), "mageshop_yapay_cc_request.log");
        if(empty($response) || $response == null){
            Mage::throwException($_helper->__("Algo não ocorreu bem. Por favor verifique suas informações ou altere a forma de pagamento."));
        }
        json_encode($response);
        $info->setAdditionalInformation("transactions", $response);
        return json_decode($response, true);
    }
    public function ccData()
    {
        return new MageShop_Yapay_Model_Payment_Transactions_Raw;
    }

}
