<?php

class DpTD_Catalog_Block_Category_Block extends Mage_Core_Block_Template {

  protected $_products;
  protected $_category;

  public function getProductsCollection() {
    if ($this->_products == null) {
      $this->_products = Mage::getModel('catalog/product')->getCollection()
                            ->addAttributeToSelect('*')
                            ->addCategoryFilter($this->getCategory())
                            ->setPageSize(5)
                            ->setCurPage(1);
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