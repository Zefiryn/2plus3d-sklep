<?php
class DpTD_Catalog_Helper_Data extends Mage_Catalog_Helper_Data
{
  public function getFormatedDate($date) {
    return Mage::helper('site')->ago($date);
  }

  public function getProductCategoryName($product) {
    return $this->_getProductCategory($product)->getName();
  }
  
  public function getProductCategoryLink($product) {
    return $this->_getProductCategory($product)->getUrlPath();
  }
  
  protected function _getProductCategory($product) {
    $magazineCat = Mage::getModel('catalog/category')->load('kwartalnik', 'url_key');
    $bookCat = Mage::getModel('catalog/category')->load('ksiazki', 'url_key');
    
    foreach($product->getCategoryIds() as $categoryId) {
      if ($categoryId == $magazineCat->getId()) {
        return $magazineCat;
      }
      elseif ($categoryId == $bookCat->getId()){
        return $bookCat;
      }
    }
  }
}