<?php 

/**
 * Yapay Transparente Magento
 * Updater Model Class - Update order to/from yapay
 *
 * @category    MageShop
 * @package     MageShop_Yapay
 * @author      Vitor Costa
 * @copyright   Copyright (c)  2018 mageshop (https://www.mageshop.com.br/ | https://github.com/csvitor/MageShop_Yapay)
 */
class MageShop_Yapay_Model_Payment_Updater extends Mage_Core_Model_Abstract{

    const YAPAY_TRANSACTION_CODE_STATE_PENDING_PAYMENT = 4;
    const YAPAY_TRANSACTION_CODE_STATE_PAID = 6;
    const YAPAY_TRANSACTION_CODE_STATE_CANCELED = 7;
    const YAPAY_TRANSACTION_CODE_STATE_REVIEW = 24;
    const YAPAY_TRANSACTION_CODE_STATE_MONITORING = 87;
    const YAPAY_SUCCESS = 'success';

    protected $_data;
    protected $_transaction;
    protected $_order;
    protected $_helper;
    protected $_order_number;

    public function processOrder($responseYapay)
    {
        $this->_helper = Mage::helper("mageshop_yapay/data");
        if(empty($responseYapay) || $responseYapay == null){
            Mage::throwException(
                $this->_helper->__("Falha ao processar o pedido." . (var_export($responseYapay, true)))
            );
        }
        if(gettype($responseYapay) == 'string'){
            $this->_data = json_decode( $responseYapay , true );
        }else{
            $this->_data = $responseYapay;
        }
        if(!$this->_data['data_response']['transaction']){
            Mage::throwException(
                $this->_helper->__("transaction não encontrado." . (var_export($responseYapay, true)))
            );
        }
        if( $this->_data['message_response']['message'] !== self::YAPAY_SUCCESS ){
            Mage::throwException(
                $this->_helper->__("Erro." . (var_export($responseYapay, true)))
            );
        }
        $this->_transaction = $this->_data['data_response']['transaction'];
        $this->_order_number = $this->_transaction['order_number'];
        $this->processState();
    }

    public function _getOrderId()
    {
        return $this->_order_number;
    }

    public function _order()
    {
        if(!$this->_order){
            $this->_order = Mage::getModel('sales/order')->loadByIncrementId($this->_getOrderId());
        }
        return $this->_order;
    }

    /**
     * Processes order status and return information about order status and state
     * Doesn' change anything to the order. Just returns an object showing what to do.
     *
     * @param string|int $statusCode
     * @return Varien_Object
     * @throws Varien_Exception
     */
    public function processState()
    {
        $this->_order();
        $cod_status = (int) $this->_transaction['status_id'];
        $comment =  $cod_status . ' - ' . $this->_transaction['status_name'];

        switch($cod_status)
        {
            case self::YAPAY_TRANSACTION_CODE_STATE_PENDING_PAYMENT:
            case 5:
            case 88:
                $this->_pending($this->_helper->__('Yapay Intermediador enviou automaticamente o status: %s', $comment));
            break;
            case self::YAPAY_TRANSACTION_CODE_STATE_MONITORING:
                $this->_holded($this->_helper->__('Yapay Intermediador enviou automaticamente o status: %s', $comment));
            break;
            case self::YAPAY_TRANSACTION_CODE_STATE_PAID:
                $this->_paid($this->_helper->__('Yapay Intermediador - Aprovado. Confirmado automaticamente o pagamento do pedido.'));
            break;
            case self::YAPAY_TRANSACTION_CODE_STATE_CANCELED:
                $this->_cancel($this->_helper->__('Yapay Intermediador - Cancelado. Pedido cancelado automaticamente.'));
            break;
            case self::YAPAY_TRANSACTION_CODE_STATE_REVIEW:
                $this->_holded($this->_helper->__('Yapay Intermediador enviou automaticamente o status: %s', $comment));
            break;
            case 89:
                $this->_cancel(
                    $this->_helper->__('Yapay Intermediador - Reprovado. Pedido cancelado automaticamente, o pagamento foi negado.')
                );
            break;
            default:
               $this->_status(
                $this->_helper->__('Codigo de status inválido retornado pela Yapay. (' . $cod_status . ')')
               );
        }
    }
    
    public function _status($comment)
    {
        // update state yapay
        $this->updateInfo();
        $order = $this->_order();
        $order->setState($order->getState(), true);
        $order->setStatus($order->getState());
        $order->addStatusHistoryComment($comment, false);
        $order->save();
    }

    public function _paid($comment)
    {
        // check if the order can be invoiced and status pedding or new
        $order = $this->_order();
        $this->is_holded();
        // Check if the order can be invoiced
        if(!$order->canInvoice()) {
            return true;
        }
        // update state yapay
        $this->updateInfo();
        // Create the invoice
        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        $invoice->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder());
        $invoice->addComment( (string) $comment , true, true);
        $invoice->save();
        $transactionSave->save();
        // check if the order if is processing
        if($order->getState() !== Mage_Sales_Model_Order::STATE_PROCESSING){
            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
        }if($order->getState() !== Mage_Sales_Model_Order::STATE_PROCESSING){
            $order->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING);
        }
        $status_history = $order->addStatusHistoryComment($comment);
        $status_history->setIsCustomerNotified(false); // Defina como "false" se não quiser notificar o cliente
        $order->save();
        return true;
    }
    
    public function hasInvoices()
    {
        return (bool) ($this->_order()->hasInvoices()) > 0 ? true : false;
    }
    public function _cancel($comment)
    {
        $order = $this->_order();
       
        if($this->hasInvoices()){
            $this->_void($comment);
            return true;
        }

        $this->is_holded();
        
        if (!$order->canCancel()) {
            return true;
        }
        // update state yapay
        $this->updateInfo();
         // Cancelar o pedido
        $order->cancel();
        $history = $order->addStatusHistoryComment($comment, true);
        $history->setIsCustomerNotified(true); 
        // Salvar as alterações
        $order->save();
    }

    public function _pending($comment)
    {
        $this->updateInfo();
        $order = $this->_order();
        $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true);
        $order->setStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
        $order->addStatusHistoryComment($comment, false);
        $order->save();
    }

    public function updateInfo()
    {
        $order = $this->_order();
        $payment = $order->getPayment();
        $payment->setAdditionalInformation("status_id", $this->_transaction['status_id']);
        $payment->setAdditionalInformation("token_transaction", $this->_transaction['token_transaction']);
        $payment->setAdditionalInformation("status_name", $this->_transaction['status_name']);
    }

    public function _holded($comment)
    {
        $order = $this->_order();
        // update state yapay
        if ($order->getStatus() == Mage_Sales_Model_Order::STATE_HOLDED) {
            return $this;
        }
        $order->setState(Mage_Sales_Model_Order::STATE_HOLDED, true);
        $order->setStatus(Mage_Sales_Model_Order::STATE_HOLDED);
        $order->addStatusHistoryComment($comment, false);
        $order->save();
    }
    public function _void($comment)
    {
        $this->is_holded();
        $order = $this->_order();
        if (!$order->canCreditmemo()) {
            return true;
        }
        // update state yapay
        $this->updateInfo();
        $service = Mage::getModel('sales/service_order', $order);
        $creditmemo = $service->prepareCreditmemo();
        $creditmemo->setRefundRequested(true);
        $creditmemo->setOfflineRequested(true);
        $creditmemo->register();
        $creditmemo->save();
        $order->setStatus(Mage_Sales_Model_Order::STATE_CLOSED);
        $order->addStatusHistoryComment($comment, false);
        $order->save();
    }
    /**
     * Pagamento em analise
     *
     */
    public function is_holded()
    {
        if ($this->to_monitoring()) {
            if ($this->_order()->canUnhold()) {
                $this->_order()->unhold();
            }
        }
    }
    /**
     * payment review
     *
     * @return bool
     */
    public function to_monitoring()
    {
        return (bool)(
            $this->_order()->getState() == Mage_Sales_Model_Order::STATE_HOLDED || $this->_order()->getState() == Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW
        );
    }
}