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
}
