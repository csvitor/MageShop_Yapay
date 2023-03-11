<?php

class MageShop_Yapay_Model_Source_Environment {

  /*
  * Function to get environment options
  * @return array
  */
  public function toOptionArray() {
    return [
      [
        'value' => 'sandbox',
        'label' => 'Sandbox'
      ],
      [
        'value' => 'production',
        'label' => 'Production'
      ]  
    ];
  }
}
