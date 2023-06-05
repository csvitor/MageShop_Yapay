<?php 

class MageShop_Yapay_CcController extends Mage_Core_Controller_Front_Action{

    const URI_SLIT = 'v1/transactions/simulate_splitting';
    public function splitAction()
    {

        $_helper = Mage::helper("mageshop_yapay/data");
        $minSplit = $_helper->getCountMinSplit();
        $maxSplit = $_helper->getCountMaxSplit();
        $totals = Mage::getSingleton('checkout/session')->getQuote()->collectTotals()->getGrandTotal();
        $subTotal = Mage::getSingleton('checkout/session')->getQuote()->collectTotals()->getSubtotal();
        $_discount_helper = Mage::helper("mageshop_yapay/discount");
        $value_discount = 0;
        $percentage = 0;
        if($_discount_helper->getDiscountActiveCreditCard()){
            $percentage = $_discount_helper->getDiscountPercentageCreditCard();
            if ($percentage > 0 && $percentage < 100) {
                $value_discount = $_helper->monetize($percentage * 0.01 * $subTotal);
            }
        }
        $rest = $this->restCurl();

        $price = number_format( (float) $totals , 2, '.','');
        $params = array(
            "token_account" => $_helper->getToken(),
            "price" => $price
        );
        $raw = json_encode($params);
        $_url =  $_helper->getUrlEnvironment() . self::URI_SLIT;
        $rest->url($_url)->_method('POST')->_body($raw)->exec();
        $method = $this->getRequest()->getParam('method', false);
        $tcResponse = simplexml_load_string($rest->getResponse());
        foreach ($tcResponse->data_response->payment_methods->payment_method as $payment_method){            
            if(intval($payment_method->payment_method_id) == intval($method)){
                $splittings = $payment_method->splittings->splitting;
            }
        }
        for($auxS = 0; $auxS < (int) $maxSplit && $auxS < sizeof($splittings); $auxS++){
            $splitting = $splittings[$auxS];
            if ($splitting->value_split >= floatval($minSplit)) {
                $split = (int) $splitting->split;
               
                $discounSplitOk = ($split <= $_discount_helper->getDiscountInstallmentCreditCard());
                $interest = (((float)$splitting->split_rate == 0) ? "sem" : "com") . " juros";
                
                if($value_discount > 0 && $discounSplitOk){
                    $value_split = $_helper->monetize(( $totals - $value_discount ) / $split);
                    $split_rate = $_helper->__("%s desconto de %s%% %s", number_format( (float) $value_split, 2, ',',''), $percentage, $interest);
                }else{
                    $value_split = $_helper->monetize( $splitting->value_split );
                    $split_rate = $_helper->__("%s %s", number_format( (float) $value_split, 2, ',',''), $interest);
                }
                $splitSimulate[$split] = $_helper->__("%dx de R$%s", $split, $split_rate);
            } else {
                $discounSplitOk = (1 <= $_discount_helper->getDiscountInstallmentCreditCard());
                if ($price <= floatval($minSplit)){
                    if($value_discount > 0 && $discounSplitOk){
                        $splitSimulateSplit = array(
                            "1" => "1x de R$" . (string) $price . " desconto de ".$percentage." sem juros"
                        );
                    }else{
                        $splitSimulateSplit = array(
                            "1" => "1x de R$" . (string) $price . " sem juros"
                        );
                    }
                }
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