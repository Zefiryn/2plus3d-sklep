<?php

class DpTD_Catalog_Model_Resource_Setup extends Mage_Eav_Model_Entity_Setup {

    protected function _prepareValues($attr) {
        $data = parent::_prepareValues($attr);
        $data = array_merge($data, array(
            'frontend_input_renderer'       => $this->_getValue($attr, 'input_renderer'),
            'is_global'                     => $this->_getValue(
                $attr,
                'global',
                Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL
            ),
            'is_visible'                    => $this->_getValue($attr, 'visible', 1),
            'is_searchable'                 => $this->_getValue($attr, 'searchable', 0),
            'is_filterable'                 => $this->_getValue($attr, 'filterable', 0),
            'is_comparable'                 => $this->_getValue($attr, 'comparable', 0),
            'is_visible_on_front'           => $this->_getValue($attr, 'visible_on_front', 0),
            'is_wysiwyg_enabled'            => $this->_getValue($attr, 'wysiwyg_enabled', 0),
            'is_html_allowed_on_front'      => $this->_getValue($attr, 'is_html_allowed_on_front', 0),
            'is_visible_in_advanced_search' => $this->_getValue($attr, 'visible_in_advanced_search', 0),
            'is_filterable_in_search'       => $this->_getValue($attr, 'filterable_in_search', 0),
            'used_in_product_listing'       => $this->_getValue($attr, 'used_in_product_listing', 0),
            'used_for_sort_by'              => $this->_getValue($attr, 'used_for_sort_by', 0),
            'apply_to'                      => $this->_getValue($attr, 'apply_to'),
            'position'                      => $this->_getValue($attr, 'position', 0),
            'is_configurable'               => $this->_getValue($attr, 'is_configurable', 1),
            'is_used_for_promo_rules'       => $this->_getValue($attr, 'used_for_promo_rules', 0)
        ));
        return $data;
    }

    public function getDefaultEntities() {
        return array(
          'catalog_product' => array(
            'entity_model' => 'catalog/product',
            'attribute_model' => 'catalog/resource_eav_attribute',
            'table' => 'catalog/product',
            'additional_attribute_table' => 'catalog/eav_attribute',
            'entity_attribute_collection' => 'catalog/product_attribute_collection',
            'attributes' => array(
              'publish_date' => array(
                'type' => 'datetime',
                'label' => 'Publish date',
                'input' => 'date',
                'backend' => 'eav/entity_attribute_backend_datetime',
                'source' => null,
                'default' => '0',
                'sort_order' => 10,
                'visible_on_front' => 1,
                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                'searchable' => false,
                'used_in_product_listing' => true,
              ),
              'format' => array(
                'type' => 'varchar',
                'label' => 'Format',
                'input' => 'text',
                'required' => false,
                'sort_order' => 11,
                'visible_on_front' => 1,
                'backend' => null,
                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'filterable' => false,
                'used_in_product_listing' => true,
              ),
              'page_no' => array(
                'type' => 'varchar',
                'label' => 'Number of pages',
                'input' => 'text',
                'required' => false,
                'sort_order' => 12,
                'visible_on_front' => 1,
                'backend' => null,
                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'filterable' => false,
                'used_in_product_listing' => true,
              ),
              'isbn' => array(
                'type' => 'varchar',
                'label' => 'ISBN/ISSN',
                'input' => 'text',
                'required' => false,
                'sort_order' => 13,
                'visible_on_front' => 1,
                'backend' => null,
                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'filterable' => false,
                'used_in_product_listing' => true,
              ),
              'binding' => array(
                'type' => 'varchar',
                'label' => 'Binding type',
                'input' => 'select',
                'required' => false,
                'sort_order' => 14,
                'visible_on_front' => 1,
                'backend' => null,
                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'filterable' => true,
                'option' => array('values' => array('hardcover', 'paperback')),
              ),
            ),
          )
        );
    }

}