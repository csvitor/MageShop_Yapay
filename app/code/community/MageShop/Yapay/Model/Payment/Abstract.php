<?php 

class MageShop_Yapay_Model_Payment_Abstract extends Mage_Payment_Model_Method_Abstract{

    const YAPAY_TRANSACTION_CODE_STATUS_PAID = 6;

    /**
     * Processa o status do pedido
     */
    public function proccess($responseYapay){
        // prevent this event from firing twice
        if (Mage::registry('sales_order_invoice_save_after_event_triggered')){
            return $this; // this method has already been executed once in this request
        }
        Mage::register('sales_order_invoice_save_after_event_triggered', true);
        $payment = $this->getInfoInstance();
        // tries to get order object from payment method, to get
        // flags setted on the memory but not persisted yet
        if($payment->getOrder()){
            $order = $payment->getOrder();
        }
        $processedState = $this->processStatus($responseYapay);
        $message = $processedState->getMessage();
        if ($processedState->getStateChanged()){
            $order->setState($processedState->getState(),true, $message ,$processedState->getIsCustomerNotified());
        }
        else{
            $order->addStatusHistoryComment($message)
                  ->setIsCustomerNotified($processedState->getIsCustomerNotified());
        }
        $payment->save();
        $order->save();
        return $this;
    }

     /**
     * Processes order status and return information about order status and state
     * Doesn' change anything to the order. Just returns an object showing what to do.
     *
     * @param string|int $statusCode
     * @return Varien_Object
     * @throws Varien_Exception
     */
    public function processStatus($data)
    {
        $return = new Varien_Object();
        $return->setStateChanged(true);
        $return->setIsTransactionPending(true); //payment is pending?
        $cod_status = $data['status_id'];    
        $comment =  $cod_status . ' - ' . $data['status_name'];
        $_helper = Mage::helper("mageshop_yapay/data");
        switch($cod_status)
        {
            case 4:
            case 5:
            case 88:
            case 87:
                $return->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
                $return->setIsCustomerNotified(true);
                if ($this->getCode() == MageShop_Yapay_Model_Payment_Method_Cc::PAY_CODE) {
                    $return->setStateChanged(false);
                    $return->setIsCustomerNotified(false);
                }
                $return->setMessage($_helper->__('Yapay Intermediador enviou automaticamente o status: %s', $comment));
            break;
            case self::YAPAY_TRANSACTION_CODE_STATUS_PAID:
                $return->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
                $return->setIsCustomerNotified(true);
                $return->setMessage('Yapay Intermediador - Aprovado. Confirmado automaticamente o pagamento do pedido.');
                $return->setIsTransactionPending(false);
            break;
            
            case 7:
                $return->setState(Mage_Sales_Model_Order::STATE_CANCELED);
                $return->setIsCustomerNotified(true);
                $return->setMessage('Yapay Intermediador - Cancelado. Pedido cancelado automaticamente.');
                
            case 24:
                $return->setState(Mage_Sales_Model_Order::STATE_HOLDED);
                $return->setIsCustomerNotified(false);
                $return->setMessage($_helper->__('Yapay Intermediador enviou automaticamente o status: %s', $comment));
            break;
            case 89:
                // Pagamento Reprovado
                $comment = 'Yapay Intermediador - Reprovado. Pedido cancelado automaticamente, o pagamento foi negado.';
                $return->setState(Mage_Sales_Model_Order::STATE_HOLDED);
                $return->setIsCustomerNotified(false);
                $return->setMessage($_helper->__($comment));
            break;
            default:
                $return->setIsCustomerNotified(false);
                $return->setStateChanged(false);
                $return->setMessage('Codigo de status inválido retornado pela Yapay. (' . $cod_status . ')');
        }
        return $return;
    }

    /**
     * Verfica se houve erro na requisição
     *
     * @return array
     */
    public function _validity($resYapay){
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
}
