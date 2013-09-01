<?php
$installer = $this;
$installer->startSetup();

$attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product','issn');
if ($attributeId) {
    $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
    $attribute->setIsUnique(0)->save();
}

$installer->endSetup();