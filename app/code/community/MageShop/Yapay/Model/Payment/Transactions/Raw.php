<?php 

class MageShop_Yapay_Model_Payment_Transactions_Raw{

    private $_helper;
    private $_helperDoc;
    private $_helperCc;
    private $_data;
    private $_payment;
    private $_info;
    private $_datacc;
    private $available_payment_methods;

    public function __construct()
    {
        $this->_helper = Mage::helper("mageshop_yapay/data");
        $this->_helperDoc = Mage::helper("mageshop_yapay/documents");
    }

    public function getDataBankslip($payment, $info)
    {
        $this->_payment = $payment;
        $this->_info = $info;
        $this->_datacc = json_decode($this->_info->getAdditionalInformation("data"), true);
        $this->available_payment_methods = "6";
        $this
        ->token_account()
        ->customer()
        ->contacts()
        ->shippingAddress()
        ->billingAddress()
        ->transaction_product()
        ->transaction()
        ->paymentBankslip();
        return $this->_data;
    }
    public function getDataPix($payment, $info)
    {
        $this->_payment = $payment;
        $this->_info = $info;
        $this->_datacc = json_decode($this->_info->getAdditionalInformation("data"), true);
        $this->available_payment_methods = "27";
        $this
        ->token_account()
        ->customer()
        ->contacts()
        ->shippingAddress()
        ->billingAddress()
        ->transaction_product()
        ->transaction()
        ->paymentPix();
        return $this->_data;
    }
    public function getDataCc($payment, $info)
    {
        $this->_payment = $payment;
        $this->_info = $info;
        $this->_helperCc = Mage::helper("mageshop_yapay/cc");
        $this->_datacc = json_decode($this->_info->getAdditionalInformation("data"), true);
        $this->available_payment_methods = $this->_helper->getAvailablePaymentMethodsCc();
        $this
        ->token_account()
        ->fingerPrint()
        ->customer()
        ->contacts()
        ->shippingAddress()
        ->billingAddress()
        ->transaction_product()
        ->transaction()
        ->paymentCc();
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
        $shippingTitle = $this->_payment->getOrder()->getData('shipping_description');
        $quote = $this->_payment->getOrder()->getQuote(); // obtém o objeto quote do pedido
        if($shippingTitle == NULL || empty($shippingTitle)){
            $shippingTitle = "Produto Virtual";
        }
        $shippingPrice = $this->_payment->getShippingAmount();
        $discountAmount = $this->_payment->getOrder()->getDiscountAmount();
        $discountAmountCc = $this->_payment->getOrder()->getQuote()->getYapayDiscount();
        $discount = $this->_helper->monetize(-1 * ($discountAmount + $discountAmountCc));
        $additional = $this->_payment->getOrder()->getQuote()->getYapayInterest();
        $this->_data ["transaction"] = array(
            "available_payment_methods" => $this->available_payment_methods,
            "customer_ip" => Mage::helper('core/http')->getRemoteAddr(),
            "shipping_type" => $shippingTitle,
            "shipping_price" => is_numeric( $shippingPrice ) ? sprintf('%.2f', $shippingPrice) : $shippingPrice,
            "price_discount" => ($discount > 0) ? sprintf('%.2f', $discount) : 0,
            "price_additional" => ($additional > 0) ? sprintf('%.2f', $additional) : 0,
            "url_notification" => Mage::getUrl('yapay/observer/postback'),
            "free" => "mod_m1_mageshop",
            "order_number" => $this->_payment->getOrder()->getQuote()->getReservedOrderId()
        );
        return $this;
    }

    private function paymentBankslip()
    {
        $this->_data['payment'] =  array(
            "payment_method_id" => "6",
            "billet_date_expiration" => "03/06/2023",
        );
        return $this;
    }
    private function paymentPix()
    {
        $this->_data['payment'] =  array(
            "payment_method_id" => "27",
            "split" => "1"
        );
        return $this;
    }
    private function paymentCc()
    {
        $numberCc = preg_replace('/[^0-9]/is', '', $this->_datacc['yapay_creditcardpayment_cc_number']);
        $idCc = $this->_helperCc->getCardTypeYapay($numberCc);
        $nameCc = $this->_datacc['yapay_creditcardpayment_cc_number'];
        $cvvCc = $this->_datacc['yapay_creditcardpayment_cc_cid'];
        $splitNumberCc = $this->_datacc['yapay_creditcardpayment_cc_split_number'];
        $expdate_month = $this->_datacc['yapay_creditcardpayment_cc_expdate_month'];
        $expdate_year = $this->_datacc['yapay_creditcardpayment_cc_expdate_year'];
        $this->expdate(  $expdate_month ,  $expdate_year );
        $this->_data['payment'] =  array(
            "payment_method_id" => $idCc,
            "card_name" => $nameCc,
            "card_number" => $numberCc,
            "card_expdate_month" => $expdate_month,
            "card_expdate_year" => $expdate_year,
            "card_cvv" => $cvvCc,
            "split" => $splitNumberCc
        );
        return $this;
    }

    private function expdate($expiryMonth , $expiryYear)
    {
        $currentDate = date('m/Y'); // Obtém a data atual no formato "mm/aaaa"
        // Separa o mês e o ano da data atual
        $currentMonth = substr($currentDate, 0, 2);
        $currentYear = substr($currentDate, 3);

        if (($expiryMonth < $currentMonth && $expiryYear == $currentYear) || ($expiryMonth == $currentMonth && $expiryYear < $currentYear)) {
            // O mês do cartão de crédito é menor que o mês atual ou o ano do cartão de crédito é menor que o ano atual
            // Retorna um erro indicando que a data de validade do cartão de crédito é inválida
            Mage::throwException("A data de validade do cartão de crédito é inválida.");
        }

    }

    private function customer()
    {
        $quote = $this->getCheckout()->getQuote();
        if(isset($this->_datacc['yapay_form_cpf'])){
            $number_taxvat = $this->_datacc['yapay_form_cpf'];
        }else{
            $attrSelectedCPF = $this->_helperDoc->getAttrCpfArray();
            if($attrSelectedCPF != null && isset($attrSelectedCPF[0], $attrSelectedCPF[1])){
                switch ($attrSelectedCPF[0]) {
                    case 'customer':
                        $number_taxvat = $quote->getCustomer()->getData($attrSelectedCPF[1]);
                        break;
                    case 'billing':
                        $number_taxvat = $quote->getBillingAddress()->getData($attrSelectedCPF[1]);
                        break;
                }
            }else{
                $number_taxvat = $quote->getCustomerTaxvat();
            }
        }
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
        $quote = $this->getCheckout()->getQuote();
        $items = $quote->getAllItems();
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