<?php 

/**
 * Yapay Transparente Magento
 * Yapay Abstract Model Class - Used on processing and sending information to/from Yapay
 *
 * @category    MageShop
 * @package     MageShop_Yapay
 * @author      Vitor Costa
 * @copyright   Copyright (c)  2018 mageshop (https://www.mageshop.com.br/ | https://github.com/csvitor/MageShop_Yapay)
 */
class MageShop_Yapay_Model_Payment_Abstract extends Mage_Payment_Model_Method_Abstract{

    /**
     * Verfica se houve erro na requisição
     *
     * @return array
     */
    public function _validity($resYapay){

        $response['general_errors'] = false;
        $message = $resYapay['message_response']['message'];
        $additionalData = isset($resYapay['error_response']['additional_data']) ? $resYapay['error_response']['additional_data'] : null;
        $additionalData = ($additionalData == null && isset($resYapay['additional_data'])) ? $resYapay['additional_data'] : $additionalData;

        switch ($message) {
            case 'error':
                if(isset($resYapay['error_response']['general_errors'])){
                    $general_errors = $resYapay['error_response']['general_errors'];
                    $response['error'] = isset($general_errors['message']) ? $general_errors['message'] : "";
                    $response['code'] = isset($general_errors['code']) ? $general_errors['code'] : 0;
                }
                if(isset($resYapay['error_response']['general_errors'][0])){
                    $general_errors = $resYapay['error_response']['general_errors'][0];
                    $response['error'] = $general_errors['message'];
                    $response['code'] = $general_errors['code'];
                }
                if(isset($resYapay['error_response']['validation_errors'])){
                    $validation_errors = $resYapay['error_response']['validation_errors'];
                    $response['error'] = isset($validation_errors['message']) ? $validation_errors['message'] : '';
                    $response['code'] = isset($validation_errors['code']) ? $validation_errors['code'] : '';
                }
                if(isset($resYapay['error_response']['validation_errors'][0])){
                    $validation_errors = $resYapay['error_response']['validation_errors'][0];
                    $response['error'] = isset($validation_errors['message_complete']) ? $validation_errors['message_complete'] : $validation_errors['message'];
                    $response['code'] = $validation_errors['code'];
                }
                if( empty($additionalData['transaction_id']) || $additionalData['transaction_id'] == null){
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
                $response['payment'] = $data_response['payment'];
                $response['transaction'] = $data_response;
        }
        return $response;
    }
}
