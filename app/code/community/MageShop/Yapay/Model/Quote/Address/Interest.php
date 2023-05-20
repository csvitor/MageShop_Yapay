<?php 

class MageShop_Yapay_Model_Quote_Address_Interest extends Mage_Sales_Model_Quote_Address_Total_Abstract{

    private $_helper;
    /**
     * Constructor that should initiaze
     */
    public function __construct()
    {
        $this->setCode('mageshop_yapay_insterest'); //
        $this->_helper = Mage::helper("mageshop_yapay/interest");
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
         $apply = $this->_helper->applyInterest($address->getQuote()->getYapayCcSplitNumber());
         $ammount = $address->getQuote()->getYapayInterest();
         if ($ammount > 0 && $ammount != null && $apply && $paymentCcYapay) {
             $this->_setBaseAmount($ammount);
             $this->_setAmount($address->getQuote()->getStore()->convertPrice($ammount, false));
             $address->setYapayInterest($ammount);
             $address->setYapayBaseDiscount($ammount);
         } else {
             $this->_setBaseAmount(0.00);
             $this->_setAmount(0.00);
             $address->setYapayInterest(0.00);
             $address->setYapayBaseInterest(0.00);
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
         if ($address->getYapayInterest() != 0 && $address->getAddressType() == 'shipping') {
            $address->addTotal(array
            (
                'code' => $this->getCode(),
                'title' => $this->_helper->__("Juros de %d%%", $this->_helper->percentage),
                'value' => $address->getYapayInterest(),
            ));
         }
 
     }

}