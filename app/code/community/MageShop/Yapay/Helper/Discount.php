<?php

class MageShop_Yapay_Helper_Discount extends MageShop_Yapay_Helper_Data
{
    const MS_YAPAY_CC_DISCOUNT_ENABLE = "payment/yapay_creditcardpayment/discount_active";
    const MS_YAPAY_CC_DISCOUNT_PERCENTAGE = "payment/yapay_creditcardpayment/discount_percentage";
    const MS_YAPAY_CC_DISCOUNT_INSTALLMENT = "payment/yapay_creditcardpayment/discount_installment";
    const MS_YAPAY_CC_DISCOUNT_LABEL = "payment/yapay_creditcardpayment/discount_label";

    public function getDiscountActiveCreditCard(){
        return (bool) Mage::getStoreConfig(self::MS_YAPAY_CC_DISCOUNT_ENABLE);
    }
    public function getDiscountPercentageCreditCard(){
        return (double) Mage::getStoreConfig(self::MS_YAPAY_CC_DISCOUNT_PERCENTAGE);
    }
    public function getDiscountInstallmentCreditCard(){
        return (double) Mage::getStoreConfig(self::MS_YAPAY_CC_DISCOUNT_INSTALLMENT);
    }
    public function getDiscountLabelCreditCard(){
        return $this->__(Mage::getStoreConfig(self::MS_YAPAY_CC_DISCOUNT_LABEL), $this->getDiscountPercentageCreditCard());
    }

    public function setDiscountCc($info){

        $valor_desconto = 0;
        $grandTotal = $info->getQuote()->getGrandTotal();
        $percentage = $this->getDiscountPercentageCreditCard();
        if ($percentage > 0 && $percentage < 100) {
            $valor_desconto = $this->monetize(-1 * $percentage * 0.01 * $grandTotal);
        }

        if ($valor_desconto < 0) {
            $info->getQuote()->setYapayDiscount($valor_desconto);
            $info->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
        }

    }

    

}
