<?php

class MageShop_Yapay_Block_Form_Creditcard extends Mage_Payment_Block_Form
{
    protected $_helper;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mageshop/yapay/form/creditcard.phtml');
        $this->_helper = Mage::Helper("mageshop_yapay/data");
    }
    /**
     * Function to get token
     */
    public function getFullToken()
    {
      return $this->_helper->getToken();
    }
    /**
     * Function to get token
     */
    public function getServiceUrl()
    {
        return $this->_helper->getUrlEnvironment();
    }
    /**
     * Function to get years cc
     *
     * @return array
     */
    public function years()
    {
      $dateTime = Mage::getModel('core/date')->timestamp(time());
      $anoAtual = date('Y', $dateTime);
      $card_expdate_years = array();
      for ($i=0; $i < 14; $i++) { 
          $nextYear = $anoAtual + $i;
          array_push($card_expdate_years, $nextYear);
      }
      return $card_expdate_years;
    }

    /**
     * Function to get installments
     * @param mixed $total
     */
    public function getInstallments($total)
    {
      $maxInstallments = $this->_helper->getCountMaxSplit();
      $minValueInstalment = $this->_helper->getCountMinSplit();
      $dataInterest = $this->_helper->getInstallmentInterest();
      $dataInterest = unserialize($dataInterest);

      $_discount_helper = Mage::helper("mageshop_yapay/discount");
      $value_discount = 0;
      $percentage = 0;
      if($_discount_helper->getDiscountActiveCreditCard()){
        $percentage = $_discount_helper->getDiscountPercentageCreditCard();
        if ($_discount_helper->percentage()) {
            $value_discount = $_discount_helper->getDiscountCc($total);
        }
      }

      foreach ($dataInterest as $key => $value) {
        $installmentInterest[] = $value['from_qty'];
      }
      $arrayInstallments[0] = "Select Installment";
      if ($maxInstallments == 0) {
        $arrayInstallments[] = "1x de R$$total sem juros";
      }
      for ($i = 0; $i < $maxInstallments; $i++) {
        $times = $i + 1;
        $discounLabel = '';
    
        if($value_discount > 0 && $_discount_helper->getSplitOk(($i + 1))){
          $valuePortion = $this->_helper->monetize(( $total - $value_discount ) / ($i + 1));
          $valuePortion = number_format($valuePortion, 2);
          $discounLabel = $this->_helper->__(" desconto de %s%%", $percentage);
        }else{
          $valuePortion = ($total / ($i + 1));
          $valuePortion = number_format($valuePortion, 2);
        }

        if (($i + 1) == 1) {
          if ($installmentInterest[0] == 0 || $installmentInterest[0] == null) {
            $arrayInstallments[] = $this->_helper->__("1x de R$$valuePortion%s sem juros", $discounLabel);
          } else {
            $interest = $valuePortion * ($installmentInterest[0] / 100);
            $interest = number_format($interest, 2);
            $valuePortion = $valuePortion + $interest;
            $valuePortion = number_format($valuePortion, 2);
            $totalInterest = $interest * ($i + 1);
            $totalInterest = number_format($totalInterest, 2);
            $arrayInstallments[] = $this->_helper->__("1x de R$$valuePortion%s com juros total de R$$totalInterest", $discounLabel);
          }
        } else if (isset($installmentInterest[$i]) && $installmentInterest[$i] != 0 && $installmentInterest[$i] != null) {
          $valuePortion = ($total / ($i + 1));
          if ($valuePortion >= $minValueInstalment) {
            $interest = $valuePortion * ($installmentInterest[$i] / 100);
            $interest = number_format($interest, 2);
            $valuePortion = $valuePortion + $interest;
            $valuePortion = number_format($valuePortion, 2);
            $totalInterest = $interest * ($i + 1);
            $totalInterest = number_format($totalInterest, 2);
            $arrayInstallments[] = $this->_helper->__("$times" . "x de R$$valuePortion%s com juros total de R$$totalInterest", $discounLabel);
          }
        } else {
          if ($valuePortion >= $minValueInstalment) {
            $arrayInstallments[] = $this->_helper->__("$times" . "x de R$$valuePortion%s sem juros", $discounLabel);
          }
        }
      }
      return $arrayInstallments;
    }

}
