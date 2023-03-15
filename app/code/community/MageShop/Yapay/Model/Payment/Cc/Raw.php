<?php 

class MageShop_Yapay_Model_Payment_Cc_Raw{

    private $_helper;
    private $_helperDoc;
    private $_helperCc;
    private $_data;
    private $_payment;
    private $_info;
    private $_datacc;

    public function __construct()
    {
        $this->_helper = Mage::helper("mageshop_yapay/data");
        $this->_helperDoc = Mage::helper("mageshop_yapay/documents");
        $this->_helperCc = Mage::helper("mageshop_yapay/cc");
    }

    public function getDataCc($payment, $info)
    {
        $this->_payment = $payment;
        $this->_info = $info;
        $this->_datacc = json_decode($this->_info->getAdditionalInformation("data"), true);
        $this
        ->token_account()
        ->fingerPrint()
        ->customer()
        ->contacts()
        ->shippingAddress()
        ->billingAddress()
        ->transaction_product()
        ->transaction()
        ->payment();
        return $this->_data;
    }

    private function token_account(){
        $this->_data['token_account'] = $this->_helper->getToken();
        return $this;
    }

    private function fingerPrint()
    {
        $this->_data['finger_print'] = $this->_datacc['yapay_creditcardpayment_cc_finger_print'];
        return $this;
    }
    private function transaction()
    {
        
        $shippingDescription = $this->_payment->getShippingDescription();
        $shippingTitle = trim(str_replace($this->_payment->getShippingAmount(), '', $shippingDescription));
        $shippingPrice = $this->_payment->getShippingAmount();
        $discountAmount = $this->_payment->getDiscountAmount();
        $this->_data ["transaction"] = array(
            "available_payment_methods" => $this->_helper->getAvailablePaymentMethodsCc(),
            "customer_ip" => Mage::helper('core/http')->getRemoteAddr(),
            "shipping_type" => $shippingTitle,
            "shipping_price" => is_numeric( $discountAmount ) ? sprintf('%.2f', $shippingPrice) : $shippingPrice,
            "price_discount" => is_numeric( $discountAmount ) ? sprintf('%.2f',$discountAmount) : 0,
            "url_notification" => Mage::getUrl('yapay/observer/postback'),
            "free" => "mod_m1_mageshop",
        );
        return $this;
    }

    private function payment()
    {
        
        $numberCc = preg_replace('/[^0-9]/is', '', $this->_datacc['yapay_creditcardpayment_cc_number']);
        $idCc = $this->_helperCc->getCardTypeYapay($numberCc);
        $nameCc = $this->_datacc['yapay_creditcardpayment_cc_number'];
        $cvvCc = $this->_datacc['yapay_creditcardpayment_cc_cid'];
        $splitNumberCc = $this->_datacc['yapay_creditcardpayment_cc_split_number'];

        $this->_data['payment'] =  array(
            "payment_method_id" => $idCc,
            "card_name" => $nameCc,
            "card_number" => $numberCc,
            "card_expdate_month" => "01",
            "card_expdate_year" => "2023",
            "card_cvv" => $cvvCc,
            "split" => $splitNumberCc
        );
        return $this;
    }

    private function customer()
    {
        $quote = $this->getCheckout()->getQuote();
        $number_taxvat = $quote->getCustomerTaxvat();
        if($this->_helperDoc->cnpj_cpf($number_taxvat) == false){
            Mage::throwException("CPF/CNPJ invalido.");
        }else{
           $doc = preg_replace("/[^0-9]/", "", $number_taxvat);
        }
        $this->_data['customer'] = array(
            "name" => $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname(),
            "birth_date" => $quote->getCustomerDob() ? Mage::helper('core')->formatDate($quote->getCustomerDob(), 'medium', false) : '',
            "cpf" => $doc,
            "email" => $quote->getCustomerEmail()
        );
        return $this;
    }
    private function contacts()
    {
        $billingAddress = $this->_payment->getOrder()->getQuote()->getBillingAddress();
        $number_contact = str_replace([" ", "(", ")", "-"],"", $billingAddress->getTelephone());
 
        $type_contact = '';
        // if (preg_match('/^[0-9]{2}[5-9]{1}[0-9]{7,8}$/',$number_contact)) {
        if (preg_match('/^\d{2}[5-9]{1}\d{7,}$/',$number_contact)) {
            $type_contact = "M";
        }
        // if (preg_match('/^[0-9]{2}[1-6]{1}[0-9]{7}$/',$number_contact)) {
        if (preg_match('/^\d{2}[1-6]{1}\d{7,}$/',$number_contact)) {
            $type_contact = "H";
        }
        if (preg_match('/^0800[0-9]{6,7}$|^0300[0-9]{6,7}$/',$number_contact)) {
            $type_contact = "W";
        }
        $this->_data['customer']['contacts'][] =
            array(
                "type_contact" => $type_contact,
                "number_contact" => $number_contact
            );
        return $this;
    }

    private function transaction_product()
    {
        $items = $this->_payment->getOrder()->getAllItems();
        $i = 0;
        foreach ($items as $item) {
            if($item->getPrice() <= 0){
                continue;
            }
            $this->_data['transaction_product'][$i]['description'] = $item->getName();
            $this->_data['transaction_product'][$i]['quantity'] = $item->getQty();
            $this->_data['transaction_product'][$i]['price_unit'] = is_numeric($item->getPrice()) ? sprintf('%.2f', $item->getPrice()) : $item->getPrice();
            $this->_data['transaction_product'][$i]['code'] = $item->getId();
            $this->_data['transaction_product'][$i]['sku_code'] = $item->getId();
            $i++;
        }
        return $this;
    }

    private function billingAddress()
    {
        $quote = $this->_payment->getOrder()->getQuote(); // obtém o objeto quote do pedido
        $billingAddress = $quote->getBillingAddress(); // obtém o objeto endereço de cobrança
        $street = $billingAddress->getStreet();
        $this->_data['customer']['addresses'][] = array(
            "type_address" => "B",
            "postal_code" => $billingAddress->getPostcode(),
            "street" => $street[0],
            "number" => $street[1],
            "completion" => $street[2],
            "neighborhood" => $street[3],
            "city" => $billingAddress->getCity(),
            "state" => $billingAddress->getRegion(),
        );
        return $this;
    }

    private function shippingAddress()
    {
        $quote = $this->_payment->getOrder()->getQuote(); // obtém o objeto quote do pedido
        $shippingAddress = $quote->getShippingAddress(); // obtém o objeto endereço de cobrança
        $street = $shippingAddress->getStreet();
        $this->_data['customer']['addresses'][] = array(
            "type_address" => "D",
            "postal_code" => $shippingAddress->getPostcode(),
            "street" => $street[0],
            "number" => $street[1],
            "completion" => $street[2],
            "neighborhood" => $street[3],
            "city" => $shippingAddress->getCity(),
            "state" => $shippingAddress->getRegion(),
        );
        return $this;
    }

    /**
     * Cliente que efetuou a compra
     *
     * @return object|Mage
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }
}