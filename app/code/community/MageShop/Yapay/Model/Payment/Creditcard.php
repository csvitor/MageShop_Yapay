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
        $resYapay = $yapay_push->transitionCc($payment, $amount, $info);
        $validity = $this->_validity($resYapay);
        if($validity['general_errors']){
            Mage::throwException(Mage::helper("mageshop_yapay/data")->__($validity['error']));
            return false;
        }
        $info->setAdditionalInformation("transaction_id", $validity['transaction_id']);
        $info->setAdditionalInformation("order_number", $validity['order_number']);
        $info->setAdditionalInformation("status_id", $validity['status_id']);
        $info->setAdditionalInformation("token_transaction", $validity['token_transaction']);
        $this->_state($payment, $validity);
    }

    public function _state(Varien_Object $payment, $data)
    {
        $_helper = Mage::helper("mageshop_yapay/data");
        $order = $payment->getOrder();
        $cod_status = $data['status_id'];    
        $comment = $cod_status . ' - ' . $data['status_name'];
        switch ($cod_status){
            case 4: 
            case 5:
            case 88:
                $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, 
                 $_helper->__('Yapay Intermediador enviou automaticamente o status: %s', $comment)
                );
                $order->save();
                return $order;
                break;
            case 6:
                // Check if the order can be invoiced
                if(!$order->canInvoice()) {
                    return null;
                }
                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                $commentInvoice = 'Pagamento (fatura) ' . $invoice->getIncrementId() . ' foi criado. Yapay Intermediador - Aprovado. Confirmado automaticamente o pagamento do pedido.';
                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                $invoice->register();
                $invoice->getOrder()->setIsInProcess(true);
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
                $invoice->addComment( (string) $commentInvoice , true, true);
                $invoice->save();
                $transactionSave->save();
                $order->addStatusToHistory( $order->getState(), 
                (string)  $_helper->__('Yapay Intermediador - Status: %s', $comment) , true);
                $invoice->sendEmail(true, $commentInvoice);
                $order->save();
                return $order;
                break;
            case 24:
                $order->addStatusToHistory(
                    Mage_Sales_Model_Order::STATE_HOLDED, $_helper->__('Yapay Intermediador enviou automaticamente o status: %s', $comment)
                );
                $order->save();
                return $order;
                break;
            case 7:
                if (!$order->canCancel()) {
                    return false;
                }
                $comment = 'Yapay Intermediador - Cancelado. Pedido cancelado automaticamente.';
                $order->cancel();
                $order->addStatusToHistory($comment, true);
                $order->save();
                return $order;
            break;                   
            case 89:
                if (!$order->canCancel()) {
                    return false;
                }
                $comment = 'Yapay Intermediador - Reprovado. Pedido cancelado automaticamente, o pagamento foi negado.';
                $order->addStatusToHistory(
                    Mage_Sales_Model_Order::STATE_CANCELED, $_helper->__($comment), true
                );
                $order->sendOrderUpdateEmail(true, $comment);
                $order->cancel();
                $order->save();
                return $order;
            case 87:
                $order->addStatusToHistory(
                    Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, 
                    $_helper->__('Yapay Intermediador enviou automaticamente o status: %s', $comment)
                );
                $order->save();
                return $order;
            break;
        }
    }


    /**
     * Verfica se houve erro na requisição
     *
     * @return array
     */
    private function _validity($resYapay){
        $response['general_errors'] = false;
        $message = $resYapay['message_response']['message'];
        $additionalData = isset($resYapay['error_response']['additional_data']) ? $resYapay['error_response']['additional_data'] : null;
        switch ($message) {
            case 'error':
                if(isset($resYapay['error_response']['general_errors'])){
                    $response['error'] = $resYapay['error_response']['general_errors']['message'];
                    $response['code'] = $resYapay['error_response']['general_errors']['code'];
                }
                if(isset($resYapay['error_response']['validation_errors'])){
                    $response['error'] = $resYapay['error_response']['validation_errors']['message'];
                    $response['code'] = $resYapay['error_response']['validation_errors']['code'];
                }
                if($additionalData['transaction_id'] == null || strlen($additionalData['transaction_id']) < 1){
                    $response['general_errors'] = true;
                    return $response;
                }else{
                    $response['transaction_id']  = $additionalData['transaction_id'];
                    $response['order_number']  = $additionalData['order_number'];
                    $response['status_id'] = $additionalData['status_id'];
                    $response['token_transaction'] = $additionalData['token_transaction'];
                    $response['status_name'] = $additionalData['status_name'];
                }
                break;
            case 'success':
                $data_response = $resYapay['data_response']['transaction'];
                $response['transaction_id']  = $data_response['transaction_id'];
                $response['order_number']  = $data_response['order_number'];
                $response['status_id'] = $data_response['status_id'];
                $response['token_transaction'] = $data_response['token_transaction'];
                $response['status_name'] = $data_response['status_name'];
        }
        return $response;
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
