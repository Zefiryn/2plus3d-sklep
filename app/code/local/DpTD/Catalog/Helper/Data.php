<?php
class DpTD_Catalog_Helper_Data extends Mage_Catalog_Helper_Data
{
  protected $_categories = array();

  public function getFormatedDate($date) {
    return Mage::helper('site')->ago($date);
  }

  public function isInCategory($product, $categoryKey) {
    $include = false;
    foreach($product->getCategoryIds() as $categoryId) {
      if ($this->_getCategoryName($categoryId) == $categoryKey) {
        $include = true;
      }
    }
    return $include;
  }

  public function getProductCategoryName($product) {
    if ($this->isInCategory($product, 'ksiazki')) {
      return 'Książki';
    }
    if ($this->isInCategory($product, 'kwartalnik')) {
      return 'Kwartalnik';
    }
  }

  protected function _getCategoryName($categoryId) {
    if (!array_key_exists($categoryId, $this->_categories) || $this->_categories[$categoryId] == null) {
      $this->_categories[$categoryId] = Mage::getModel('catalog/category')->load($categoryId)->getUrlKey();
    }
    return $this->_categories[$categoryId];
  }
}