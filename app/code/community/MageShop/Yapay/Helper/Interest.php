<?php

class MageShop_Yapay_Helper_Interest extends MageShop_Yapay_Helper_Data
{
    const MS_YAPAY_CPF_FORM_CC_INTEREST_ACTIVE = "payment/yapay_creditcardpayment/interest_active";
    const MS_YAPAY_CPF_FORM_CC_INSTALLMENT_INTEREST = "payment/yapay_creditcardpayment/installment_interest";

    public $percentage;

    public function getInterestActive()
    {
        return (bool) Mage::getStoreConfig( self::MS_YAPAY_CPF_FORM_CC_INTEREST_ACTIVE );
    }
    public function getInstallmentInterest()
    {
        return Mage::getStoreConfig( self::MS_YAPAY_CPF_FORM_CC_INSTALLMENT_INTEREST );
    }

    public function applyInterest($split)
    {
        $this->percentage = $this->percentage($split);
        return $this->getPercentage();
    }

    public function percentage($split)
    {
        $i = 1;
        foreach ($this->getPercentageConfig() as $value) {
            $installmentInterest[$i] = $value['from_qty']; $i++;
        }
        return isset($installmentInterest[$split]) && $installmentInterest[$split] != 0 && $installmentInterest[$split] != null ? $installmentInterest[$split] : 0.0;
    }

    public function getPercentageConfig()
    {
        return unserialize($this->getInstallmentInterest());
    }

    public function getPercentage()
    {
        return ($this->percentage > 0 && $this->percentage < 100) ? true : false;
    }

    public function getValueInterest($total)
    {
        return $this->monetize($this->percentage * 0.01 * $total);
    }

    public function setInterestCc($info)
    {
        $value_interest = 0.0;
        if($this->getPercentage()){
            $value_interest = $this->getValueInterest($info->getQuote()->getSubtotal());
        }
        if ($value_interest > 0) {
            $info->getQuote()->setYapayInterest($value_interest);
        }else{
            $info->getQuote()->setYapayInterest(0.0);
        }
    }

}
