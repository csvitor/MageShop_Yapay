<?php 

class MageShop_Yapay_Model_Observer extends Varien_Object{

    const URI_SHIPPING = "api/v3/sales/trace";
    public function shipmentCheckout(Varien_Event_Observer $observer){
        
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        $paymentMethodName = $order->getPayment()->getData('method');
        
        
        if ($paymentMethodName == "yapay_creditcardpayment" || $paymentMethodName == "yapay_bankslip" || $paymentMethodName == "yapay_pix"){
            $_curl = new MageShop_Yapay_Service_Rest;
            $_helper = Mage::helper("mageshop_yapay/data");
            $_url =  $_helper->getUrlEnvironment() . self::URI_SHIPPING;
            $tracking = array();
            foreach ($shipment->getAllTracks() as $track) {
                $tracking['title'] = $track->getTitle();
                $tracking['number'] = $track->getNumber();
            }

            $token_transaction = $order->getPayment()->getAdditionalInformation("token_transaction");
            if (count($tracking) > 0) {
                $params["token_account"] = $_helper->getToken();
                $params["transaction_token"] = $token_transaction;
                if ((strtolower($tracking['title']) == 'correios') OR (strtolower($tracking['title']) == 'correio') OR 
                    (strtolower($tracking['title']) == 'correios-sedex') OR (strtolower($tracking['title']) == 'correios-pac')) {
                    $params["url"] = 'http://www2.correios.com.br/sistemas/rastreamento/';
                } else {
                    $params["url"] = $tracking['title']; 
                }
                $params["code"] = $tracking['number'];
                $params["posted_date"] = time();
                $raw = json_encode($params, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
                $_curl->url($_url)->_method('POST')->_body($raw)->exec();
                $response = $_curl->getResponse();
                $log = array("URL" => $_url, "POSTFIELDS" => $raw, "RESPONSE" => $response);
                $_curl->setLogYapay("RESPONSE", var_export($log, true), "mageshop_yapay_rastreio.log");
                json_encode($response);
                $results = json_decode($response, true);
                if (isset($results['resource']) && $results['resource'] == 'trace'){
                    $order->addStatusToHistory($order->getStatus(), "Código de rastreio " . $tracking['number'] . " enviado para a Yapay.", false);
                    $order->save();
                }
            } else {
                $_curl->setLogYapay("Rastreio Pedido", 'Sem Código de Rastreio!', 'mageshop_yapay_rastreio.log');
            }
        }
    }

    
}