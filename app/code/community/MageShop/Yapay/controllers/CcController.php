<?php 

class MageShop_Yapay_CcController extends Mage_Core_Controller_Front_Action{

    const URI_SLIT = 'v1/transactions/simulate_splitting';
    public function splitAction()
    {
        $_helper = Mage::helper("mageshop_yapay/data");
        $rest = $this->restCurl();
        $totals = Mage::getSingleton('checkout/session')->getQuote()->collectTotals();
        $price = number_format((float)$totals->grand_total, 2, '.','');
        $params = array(
            "token_account" => $_helper->getToken(),
            "price" => $price
        );
        $raw = json_encode($params);
        $_url =  $_helper->getUrlEnvironment() . self::URI_SLIT;
        $rest
        ->url($_url)
        ->_method('POST')
        ->_body($raw)
        ->exec();
        $method =  $this->getRequest()->getParam('method', false);
        $tcResponse = simplexml_load_string($rest->getResponse());
        foreach ($tcResponse->data_response->payment_methods->payment_method as $payment_method){            
            if(intval($payment_method->payment_method_id) == intval($method)){
                $splittings = $payment_method->splittings->splitting;
            }
        }
        for($auxS = 0; $auxS < (int) $_helper->getCountMaxSprit() && $auxS < sizeof($splittings); $auxS++){
            $splitting = $splittings[$auxS];
            if ($splitting->value_split >= floatval($_helper->getCountMinSprit())) {            
                $splitSimulate[(int)$splitting->split] = (string)$splitting->split . " x de R$" . number_format((float)$splitting->value_split, 2, ',','') . (((float)$splitting->split_rate == 0) ? " sem " : " com ") . "juros";
            } else {
                if ($price <= floatval($_helper->getCountMinSprit()))
                    $splitSimulateSplit = array(
                        "1" => "1 x de R$" . (string) $price . " sem juros"
                );
            }
        }
        if ($splitSimulateSplit != NULL ) {
            echo json_encode($splitSimulateSplit);
        } else {
            echo json_encode($splitSimulate);
        }
    }

    private function restCurl()
    {
        return new MageShop_Yapay_Service_Rest;
    }
}   