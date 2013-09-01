<?php

class DpTD_Catalog_Block_Category_Block extends Mage_Core_Block_Template {

  protected $_products;
  protected $_category;
  protected $_listOrder;
  
  public function setListOrder($string) {
    $this->_listOrder = $string;
  }

  public function getProductsCollection() {
    if ($this->_products == null) {
      $this->_products = Mage::getModel('catalog/product')->getCollection()
                            ->addAttributeToSelect('*')
                            ->addCategoryFilter($this->getCategory())
                            ->setPageSize(5) //@todo add system setting for the number of items
                            ->setCurPage(1);
      if ($this->_listOrder != null) {
        $this->_products->getSelect()->order($this->_listOrder);
      }
    }
    return $this->_products;
  }

  public function getCategory() {
    if ($this->_category == null) {
      $this->_category = Mage::getModel('catalog/category')->loadByAttribute('url_key', $this->getCategoryUrlKey());
    }
    return $this->_category;
  }
}