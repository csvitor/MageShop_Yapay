<?php

class MageShop_Yapay_Helper_Discount extends MageShop_Yapay_Helper_Data
{
    const MS_YAPAY_CC_DISCOUNT_ENABLE = "payment/yapay_creditcardpayment/discount_active";
    const MS_YAPAY_CC_DISCOUNT_PERCENTAGE = "payment/yapay_creditcardpayment/discount_percentage";
    const MS_YAPAY_CC_DISCOUNT_INSTALLMENT = "payment/yapay_creditcardpayment/discount_installment";
    const MS_YAPAY_CC_DISCOUNT_LABEL = "payment/yapay_creditcardpayment/discount_label";

    public function getDiscountActiveCreditCard()
    {
        return (bool) Mage::getStoreConfig(self::MS_YAPAY_CC_DISCOUNT_ENABLE);
    }
    public function getDiscountPercentageCreditCard()
    {
        return (double) Mage::getStoreConfig(self::MS_YAPAY_CC_DISCOUNT_PERCENTAGE);
    }
    public function getDiscountInstallmentCreditCard()
    {
        return (double) Mage::getStoreConfig(self::MS_YAPAY_CC_DISCOUNT_INSTALLMENT);
    }
    public function getDiscountLabelCreditCard()
    {
        return $this->__(Mage::getStoreConfig(self::MS_YAPAY_CC_DISCOUNT_LABEL), $this->getDiscountPercentageCreditCard());
    }

    public function setDiscountCc($info)
    {
        $value_discount = 0;
        if ($this->percentage()) {
            $value_discount = $this->getDiscountCc($info->getQuote()->getSubtotal(), false);
        }
        if ($value_discount < 0 && $this->getSplitOk((int) $info->getQuote()->getYapayCcSplitNumber())) {
            $info->getQuote()->setYapayDiscount($value_discount);
        }else{
            $info->getQuote()->setYapayDiscount(0.0);
        }
    }

    public function getSplitOk($split)
    {
        return (bool) (!empty($split) && $split <= $this->getDiscountInstallmentCreditCard());
    }
    public function percentage()
    {
        return (bool) ($this->getDiscountPercentageCreditCard() > 0 && $this->getDiscountPercentageCreditCard() < 100);
    }
    public function getDiscountCc($total, $convert = true)
    {
       return (float) $this->monetize((!$convert?-1:1) * $this->getDiscountPercentageCreditCard() * 0.01 * $total);
    }
}
