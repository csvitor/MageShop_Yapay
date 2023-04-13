<?php

class MageShop_Yapay_Helper_Internal extends MageShop_Yapay_Helper_Data{
    /**
     * @param $type
     * @return mixed
     */
    public function getFields($type = 'customer_address')
    {
        $entityType = Mage::getModel('eav/config')->getEntityType($type);
        $entityTypeId = $entityType->getEntityTypeId();
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')->setEntityTypeFilter($entityTypeId);
        return $attributes->getData();
    }
}