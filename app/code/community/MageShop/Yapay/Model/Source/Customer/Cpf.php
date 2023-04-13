<?php

class MageShop_Yapay_Model_Source_Customer_Cpf {

  /*
  * Function to get environment options
  * @return array
  */
  public function toOptionArray()
  {
      $_helper = Mage::helper('mageshop_yapay/internal');
      $fields = $_helper->getFields('customer');
      $options = array();
      $options[] = array('value'=>'','label'=> $_helper->__('Solicitar junto com os outros dados do pagamento'));

      foreach ($fields as $key => $value) {
          if (!is_null($value['frontend_label'])) {
              $options['customer|'.$value['frontend_label']] = array(
                  'value' => 'customer|'.$value['attribute_code'],
                  'label' => 'Customer: '.$value['frontend_label'] . ' (' . $value['attribute_code'] . ')'
              );
          }
      }

      $addressFields = $_helper->getFields('customer_address');
      foreach ($addressFields as $key => $value) {
          if (!is_null($value['frontend_label'])) {
              $options['address|'.$value['frontend_label']] = array(
                  'value' => 'billing|'.$value['attribute_code'],
                  'label' => 'Billing: '.$value['frontend_label'] . ' (' . $value['attribute_code'] . ')'
              );
          }
      }

      return $options;
  }
}
