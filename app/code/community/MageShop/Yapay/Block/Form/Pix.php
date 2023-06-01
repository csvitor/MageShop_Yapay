<?php

class MageShop_Yapay_Block_Form_Pix extends Mage_Payment_Block_Form
{
    protected $_helper;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mageshop/yapay/form/pix.phtml');
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
     * Retorna o helper do modulo
     *
     * @return MageShop_Yapay_Helper_Data
     */
    public function getHelper()
    {
      return $this->_helper;
    }
}
