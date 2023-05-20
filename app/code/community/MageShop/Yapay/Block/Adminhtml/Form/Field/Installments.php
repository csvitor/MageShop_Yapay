<?php

class MageShop_Yapay_Block_Adminhtml_Form_Field_Installments extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract {

  /**
   * Prepare rendering the new field by adding all the needed columns
   */
  protected function _prepareToRender() {
    $this->addColumn('from_qty', ['label' => __('Parcelamento com Juros'), 'class' => 'required-entry validate-length maximum-length-5 minimum-length-0 validate-number']);
    $this->_addAfter = false;
    $this->_addButtonLabel = __('Add');
  }
}
