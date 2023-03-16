<?php 

class MageShop_Yapay_CcController extends Mage_Core_Controller_Front_Action{

    private $rest;
    private $_helper;

    public function postbackAction()
    {
        $_helper = Mage::helper("mageshop_yapay/data");
        $rest = $this->restCurl();
        $post = new Zend_Controller_Request_Http();
        $data = $post->getRawBody();
        $data = json_decode($data, true);
        Mage::log( var_export( $data ,true) , Zend_Log::DEBUG , 'postback_yapay.log', true);
        Mage::log( "POSTBACK YAPAY" , Zend_Log::DEBUG , 'postback_yapay.log', true);
    }

    private function restCurl()
    {
        return new MageShop_Yapay_Service_Rest;
    }
}   