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
      $_discount_helper = Mage::helper("mageshop_yapay/discount");
      $_interest_helper = Mage::helper("mageshop_yapay/interest");
      $maxInstallments = $this->_helper->getCountMaxSplit();
      $minValueInstalment = $this->_helper->getCountMinSplit();
      $dataInterest = $_interest_helper->getInstallmentInterest();
      $dataInterest = unserialize($dataInterest);
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
      
      if ($maxInstallments == 0) {
        $arrayInstallments[1] = "1x de R$$total sem juros";
        return $arrayInstallments;
      }
      for ($i = 0; $i < $maxInstallments; $i++) {
        $times = $i + 1;
        $discounLabel = '';
        if($value_discount > 0 && $_discount_helper->getSplitOk(($i + 1))){
          $valuePortion = $this->_helper->monetize(( $total - $value_discount ) / ($i + 1));
          $valuePortion = number_format($valuePortion, 2);
          $discounLabel = $this->_helper->__(" desconto de %s%%", $percentage);
          $valuePortion2f = number_format($valuePortion, 2, ",", ".");
        }else{
          $valuePortion = ($total / ($i + 1));
          $valuePortion = number_format($valuePortion, 2);
          $valuePortion2f = number_format($valuePortion, 2, ",", ".");
        }
        if (($i + 1) == 1) {
          if ($installmentInterest[0] == 0 || $installmentInterest[0] == null) {
            $arrayInstallments[$times] = $this->_helper->__("1x de R$$valuePortion%s sem juros", $discounLabel);
          } else {
            $interest = $valuePortion * ($installmentInterest[0] / 100);
            $interest = number_format($interest, 2);
            $valuePortion = $valuePortion + $interest;
            $valuePortion = number_format($valuePortion, 2);
            $valuePortion2f = number_format($valuePortion, 2, ",", ".");
            $arrayInstallments[$times] = $this->_helper->__("1x de R$$valuePortion2f%s com juros", $discounLabel);
          }
        } else if (isset($installmentInterest[$i]) && $installmentInterest[$i] != 0 && $installmentInterest[$i] != null) {
          if ($valuePortion < $minValueInstalment) {
            break;
          }
          $interest = $valuePortion * ($installmentInterest[$i] / 100);
          $interest = number_format($interest, 2);
          $valuePortion = $valuePortion + $interest;
          $valuePortion = number_format($valuePortion, 2);
          $valuePortion2f = number_format($valuePortion, 2, ",", ".");
          $arrayInstallments[$times] = $this->_helper->__("$times" . "x de R$$valuePortion2f%s com juros", $discounLabel);
        } else {
          if ($valuePortion < $minValueInstalment) {
            break;
          }
          $arrayInstallments[$times] = $this->_helper->__("$times" . "x de R$$valuePortion2f%s sem juros", $discounLabel);
        }
      }
      return $arrayInstallments;
    }
    /**
     * Retorna o helper do modulo
     *
     * @return MageShop_Yapay_Helper_Data
     */
    public function getHelper()
    {
      return $this->_helper;
    }
}
