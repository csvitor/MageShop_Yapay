<?php 

class MageShop_Yapay_ObserverController extends Mage_Core_Controller_Front_Action{

    const URI_GET_TRANSACTION = "v2/transactions/get_by_token";
    const TYPE_RESPONSE = 'J';
    public function postbackAction()
    {
        $http = $this->getResponse();
        try {
            $data = $this->getRequest()->getPost();
            if(gettype( $data ) == 'string'){
                $data = json_decode($data, true);
            }
            $logger["RESPONSE_YAPAY_POSTBACK"] = $data;
            $_helper = Mage::helper("mageshop_yapay/data");
            $rest = $this->restCurl();
            $_url =  $_helper->getUrlEnvironment() . self::URI_GET_TRANSACTION;
            $pushData = [
                "token_transaction" => $data['token_transaction'],
                "token_account" => $_helper->getToken(),
                "type_response" => self::TYPE_RESPONSE
            ];
            $raw = json_encode($pushData, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
            $rest->url($_url)->_method('POST')->_body($raw)->exec();
            $response = $rest->getResponse();
            $order = Mage::getModel("mageshop_yapay/payment_updater");
            $order->processOrder($response);
            $logger['RESPONSE_YAPAY_AFTER_POSTBACK'] = $response;
            // registra
            $rest->setLogYapay("POSTBACK", json_encode($logger), "mageshop_yapay_postback.log");
            $http->setHttpResponseCode(200);
        }catch(Mage_Core_Exception $e){
            $http->setHttpResponseCode(400);
            $_helper->setLogAllEvents('mageshop_yapay_erro_postback.log', $e->getMessage());
        }catch (\Exception $e) {
            $http->setHttpResponseCode(400);
            $_helper->setLogAllEvents('mageshop_yapay_erro_postback.log', json_encode($e));
        }catch( \Throwable $th) {
            $http->setHttpResponseCode(500);
            $_helper->setLogAllEvents('mageshop_yapay_erro_postback.log', json_encode($th));
        }
    }

    private function restCurl()
    {
        return new MageShop_Yapay_Service_Rest;
    }
}   