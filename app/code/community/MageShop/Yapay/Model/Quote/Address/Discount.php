<?php 

class MageShop_Yapay_Model_Quote_Address_Discount extends Mage_Sales_Model_Quote_Address_Total_Abstract{

    private $_helper;
    /**
     * Constructor that should initiaze
     */
    public function __construct()
    {
        $this->setCode('mageshop_yapay_desconto'); //
        $this->_helper = Mage::helper("mageshop_yapay/discount");
    }

     /**
     * Used each time when collectTotals is invoked
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Your_Module_Model_Total_Custom
     */

     public function collect(Mage_Sales_Model_Quote_Address $address)
     {
         parent::collect($address);
         if ($address->getData('address_type') == 'billing') {
             return $this;
         }
         $paymentCcYapay = ($address->getQuote()->getPayment()->getMethod() == MageShop_Yapay_Model_Payment_Method_Cc::PAY_CODE);
         $split = (int) $address->getQuote()->getYapayCcSplitNumber();
         $splitOk = ($split <= $this->_helper->getDiscountInstallmentCreditCard());
         $ammount = $address->getQuote()->getYapayDiscount();
         if ($ammount < 0 && $ammount != null && $splitOk && $paymentCcYapay) {
             $this->_setBaseAmount($ammount);
             $this->_setAmount($address->getQuote()->getStore()->convertPrice($ammount, false));
             $address->setYapayDiscount($ammount);
             $address->setYapayBaseDiscount($ammount);
         } else {
             $this->_setBaseAmount(0.00);
             $this->_setAmount(0.00);
             $address->setYapayDiscount(0.00);
             $address->setYapayBaseDiscount(0.00);
         }
         return $this;
     }
 
     /**
      * Used each time when totals are displayed
      *
      * @param Mage_Sales_Model_Quote_Address $address
      * @return Your_Module_Model_Total_Custom
      */
     public function fetch(Mage_Sales_Model_Quote_Address $address)
     {
         if ($address->getYapayDiscount() != 0 && $address->getAddressType() == 'shipping') {
             $model = Mage::getModel("mageshop_yapay/payment_method_cc");
             $desconto = $model->getYapayDiscount($address->getQuote());
             $address->addTotal(array
                 (
                     'code' => $this->getCode(),
                     'title' => Mage::helper('checkout')->__($this->_helper->getDiscountLabelCreditCard()),
                     'value' => $address->getYapayDiscount(),
                 ));
         }
 
     }

}