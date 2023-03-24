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
    
    protected $_code = self::PAY_CODE;
    protected $_formBlockType = 'mageshop_yapay/form_creditcard';
    //protected $_infoBlockType = 'ricardomartins_pagseguro/form_info_cc';
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

    protected $_helper;
    protected $_pHelper;

    public function __construct()
    {
        $this->_helper = Mage::helper('mageshop_yapay/data');
        parent::__construct();
    }
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
        $yapay_push = new MageShop_Yapay_Model_Payment_Cc_Request;
        $resYapay = $yapay_push->transitionCc($payment, $amount,  $this->getInfoInstance());
        $validity = $this->_validity($resYapay);
        if($validity['general_errors']){
            Mage::throwException(Mage::helper("mageshop_yapay/data")->__($validity['error']));
            return $this;
        }
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
        $dataAssign = $this->saveCcAssignData($data);
        $info->setAdditionalInformation("data", $dataAssign);
        return $this;
    }
    /**
     * Function to save assign data
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
            'yapay_creditcardpayment_cc_finger_print'       => $data['yapay_creditcardpayment_cc_finger_print']
        ];
        return json_encode($array);
    }
}
