<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->addAttribute('catalog_category', 'enable_product_links', array(
    'group'         => 'Display Settings',
    'input'         => 'select',
    'type'          => 'int',
    'source'        => 'eav/entity_attribute_source_boolean',
    'label'         => 'Enable Product Links',
    'backend'       => '',
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => true,
    'default'       => 1,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));
 
foreach (Mage::getModel('catalog/category')->getCollection()->addAttributeToSelect('*') as $category) {
  
  if ($category->getData('url_key') != 'prenumerata') {
    $category->setData('enable_product_links', 1)->save();
  }
  else {
    $category->setData('enable_product_links', 0)->save();
  }
}

$installer->endSetup();
    