<?php
class MageShop_Yapay_Block_Info_Bankslip extends MageShop_Yapay_Block_Payment_Status
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mageshop/yapay/payment/info/bankslip.phtml');
    }
}