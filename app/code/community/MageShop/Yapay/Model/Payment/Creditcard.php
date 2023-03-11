<?php
class MageShop_Yapay_Model_Payment_Creditcard extends Mage_Payment_Model_Method_Abstract
{
    const PAY_CODE = 'yapay_creditcardpayment';
    
    protected $_code = self::PAY_CODE;
    protected $_formBlockType = 'mageshop_yapay/form_creditcard';
   // protected $_infoBlockType = 'mageshop_yapay/info_creditcard';
    protected $_canOrder = true;
    protected $_isInitializeNeeded = true;
    protected $_isGateway = true;
    protected $_allowCurrencyCode = ["BRL"];


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
        $info = $this->getInfoInstance();
        $yapay_push = new MageShop_Yapay_Model_Payment_Cc_Request;
        $yapay_push->transitionCc($payment, $amount, $info);
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
        $parts = explode('/', $data['yapay_creditcardpayment_cc_expdate']);
        $array = [
            'method' => $data['method'],
            'yapay_creditcardpayment_cc_number' => $data['yapay_creditcardpayment_cc_number'],
            'yapay_creditcardpayment_cc_name' => $data['yapay_creditcardpayment_cc_name'],  
            'yapay_creditcardpayment_cc_expdate_year' => $parts[1],
            'yapay_creditcardpayment_cc_expdate_month' => $parts[0],
            'yapay_creditcardpayment_cc_split_number' => $data['yapay_creditcardpayment_cc_split_number'],
            'yapay_creditcardpayment_cc_document' => $data['yapay_creditcardpayment_cc_document'],
        ];
        return json_encode($array);
    }
}
