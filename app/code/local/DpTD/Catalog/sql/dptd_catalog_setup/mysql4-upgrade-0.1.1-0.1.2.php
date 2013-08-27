<?php
$installer = $this;
$installer->startSetup();
$installer->addAttribute('catalog_product', 'issn', array(
    'group'             => 'General',
    'sort_order'        => 12,
    'type'              => 'varchar',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'ISSN',
    'input'             => 'text',
    'class'             => '',
    'source'            => '',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => true,
    'default'           => null,
    'searchable'        => true,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => true,
    'visible_in_advanced_search' => true,
    'unique'            => true,
    'used_in_product_listing' => true,
));
$installer->addAttribute('catalog_product', 'magazine_number', array(
    'group'             => 'General',
    'sort_order'        => 16,
    'type'              => 'int',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Magazine number',
    'input'             => 'text',
    'class'             => '',
    'source'            => '',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => true,
    'default'           => null,
    'searchable'        => true,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => true,
    'visible_in_advanced_search' => true,
    'unique'            => true,
    'used_in_product_listing' => true,
));
$this->removeAttribute('catalog_product', 'publish_date');
$installer->addAttribute('catalog_product', 'publish_date', array(
      'group'                       => 'General',
      'sort_order'                  => 10,
      'type'                        => 'varchar',
      'label'                       => 'Publish date',
      'input'                       => 'text',
      'backend'                     => '',
      'source'                      => null,
      'default'                     => null,
      'visible_on_front'            => true,    
      'visible_in_advanced_search'  => true,
      'global'                      => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
      'searchable'                  => false,
      'used_in_product_listing'     => true,
      'filterable'                  => false,
      'comparable'                  => false,
    
    )
);
$attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'isbn');
$attributeModel->setFrontendLabel('ISBN')->save();
$installer->endSetup();
