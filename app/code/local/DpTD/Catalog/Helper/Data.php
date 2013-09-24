<?php
class DpTD_Catalog_Helper_Data extends Mage_Catalog_Helper_Data
{
  protected $_categories;
  
  public function getFormatedDate($date) {
    return Mage::helper('site')->ago($date);
  }

  public function getProductCategoryName($product) {
    return $this->_getProductCategory($product)->getName();
  }
  
  public function getProductCategoryLink($product) {
    return $this->_getProductCategory($product)->getUrl();
  }
  
  protected function _getProductCategory($product) {
    
    if (!isset($this->_categories[$product->getId()])) {
      if ($product->getCategory()) {
        $this->_categories[$product->getId()] = $product->getCategory();
      }
      else {
        $productCat = Mage::getModel('catalog/category');
        foreach($product->getCategoryCollection() as $cat) {
          $productCat = $cat->getChildrenCount() == 0 || $cat->getLevel() < $productCat->getLevel() ? $cat : $productCat;
        }
        $this->_categories[$product->getId()] = Mage::getModel('catalog/category')->load($productCat->getId());
      }
    }    
    return $this->_categories[$product->getId()];
  }
}