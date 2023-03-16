<?php
class MageShop_Yapay_Model_Payment_Method_Cc extends MageShop_Yapay_Model_Payment_Abstract
{
    const PAY_CODE = 'yapay_creditcardpayment';
    
    protected $_code = self::PAY_CODE;
    protected $_formBlockType = 'mageshop_yapay/form_creditcard';
    //protected $_infoBlockType = 'ricardomartins_pagseguro/form_info_cc';
    protected $_isGateway = true;
    protected $_canOrder = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;
    protected $_canSaveCc = false;
    protected $_allowCurrencyCode = ["BRL"];

    protected $_helper;
    protected $_pHelper;


    public function __construct()
    {
        $this->_helper = Mage::helper('mageshop_yapay/data');
        parent::__construct();
    }

    /**
     * Order payment
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return RicardoMartins_PagSeguro_Model_Payment_Cc
     */
    public function order(Varien_Object $payment, $amount)
    {
        $yapay_push = new MageShop_Yapay_Model_Payment_Cc_Request;
        $resYapay = $yapay_push->transitionCc($payment, $amount,  $this->getInfoInstance());
        $validity = $this->_validity($resYapay);
        if($validity['general_errors']){
            Mage::throwException(Mage::helper("mageshop_yapay/data")->__($validity['error']));
            return $this;
        }
        $this->orderTransaction($payment, $amount, $validity);
        $this->proccess($validity);
        return $this;
    }

    protected function orderTransaction($payment, $amount, $returnArray){
        $isApproved = $returnArray['transaction_id'] == MageShop_Yapay_Model_Payment_Abstract::YAPAY_TRANSACTION_CODE_STATUS_PAID ? true : false;
        $transaction_id = $returnArray['transaction_id'];
        // avoid Magento transaction automatic creation to use our own logic
        $payment->setSkipOrderProcessing(true);
        // new approach: use transaction from pagseguro to 
        $transactionId = self::PAY_CODE . '-' . $transaction_id;
        $payment->setTransactionId($transactionId);
        $transactionType = $isApproved ? Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER : Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID;
        $transaction = $payment->addTransaction($transactionType);
        $transaction->setIsClosed($isApproved);
        return $this;
    }

    /**
     * Cancela o pagamento
     *
     * @param Varien_Object $payment
     * @return Mage_Payment_Model_Abstract
     */
    public function cancel(Varien_Object $payment)
    {
        // Aqui é onde você cancela o pagamento, caso a transação ainda não tenha sido capturada
        // Por exemplo, você pode verificar se a transação está autorizada no gateway e então executar o cancelamento
        $payment->setStatus(Mage_Sales_Model_Order::STATE_CANCELED)
            ->setIsTransactionClosed(1);
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
