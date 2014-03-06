<?php 

class DpTD_Catalog_Block_Listing extends Mage_Catalog_Block_Product_List {

  const _filterTypeAND = 'AND';
  const _filterTypeOR = 'OR';  
  const _modeCategory = 'CATEGORY';
  const _modeProducts = 'PRODUCTS';

  const _categoryId = 'id';
  const _categoryName = 'name';
  const _categoryKey = 'url_key';

  protected $_filters = array();
  
  /**
   * @var Mage_Catalog_Model_Category
   */
  protected $_category;
  protected $_collectionType;
  protected $_mode = 'grid';
  protected $_listCount;
  protected $_listOrder;
  protected $_showToolbar = true;
  
  public function addToFilter($attributeName, $conditionType, $attributeValue, $type = self::_filterTypeAND) {

    $this->_filters[] = array('name' => $attributeName, 'value' => $attributeValue, 'type' => $type);
  }

  public function setListCount($number) {
    $this->_listCount = $number;
  }
  public function setListOrder($string) {
    $this->_listOrder = $string;
  }

  public function setCategory($id, $type = self::_categoryId) {
    
    $category = Mage::getModel('catalog/category');
    if ($type == self::_categoryId) {
      $category = Mage::getModel('catalog/category')->load($id);
    }
    else {
      $categories = Mage::getResourceModel('catalog/category_collection');
      $categories->addAttributeToSelect('*')
                  ->addAttributeToFilter('is_active', 1)                  
                  ->setCurPage(1)->setPageSize(1);                  

      if ($type == self::_categoryName) {
        $categories->addAttributeToFilter('name', $id);
      }      
      elseif ($type == self::_categoryKey) {
        $categories->addAttributeToFilter('url_key', $id);
      }

      if ($categories->load()->getFirstItem()) {
        $category = $categories->getFirstItem();
      }      
    }
    
    $this->_category = $category;
  }
  
  public function getCategory() {
    return $this->_category != null ? $this->_category : Mage::getModel('catalog/category');
  }

  public function setCollectionType($type) {
    $this->_collectionType = $type;
  }

  public function setMode($mode) {
    $this->_mode = $mode;
    return $this;
  }
  public function getMode() {
    return $this->_mode;
  }

  public function showToolbar($val) {
    $this->_showToolbar = $val;
  }

  public function getToolbarHtml() {
    if ($this->_showToolbar == 1) {
      return parent::getToolbarHtml();
    }
    else {
      return null;
    }
  }
  
  public function getAddToCartHtml($_product) {
    Mage::unregister('product');
    Mage::register('product', $_product);
    $block = $this->getAddToCartBlock($_product);
    return $block->setTemplate('catalog/product/list/addtobox.phtml')
                  ->setProductId($_product->getId())
                  ->setProduct($_product)
                  ->toHtml();
  }
  
  public function getAddToCartBlock($_product) {
    return $this->getLayout()->createBlock('catalog/cart','product-list-addtocart-'.$_product->getId());
  }
  
  protected function _getProductCollection() {
    if ($this->_productCollection == null) {
      $mode = $this->_collectionType != null ? $this->_collectionType : $this->_getCollectionType();

      $collection = Mage::getModel('catalog/product')->getCollection();
      if ($mode == self::_modeCategory) {
        $collection = Mage::getModel('catalog/product')->getCollection()->addCategoryFilter($this->_category);
      }
      elseif ($mode != self::_modeProducts) {        
        throw new Mage_Core_Exception('No listing mode specified');
      }

      $collection->addAttributeToSelect('*');
      $collection->addMinimalPrice();
      //set items count
      $defaultElems = $this->getMode() != 'grid' ? Mage::getStoreConfig('catalog/frontend/list_per_page') : Mage::getStoreConfig('catalog/frontend/grid_per_page');
      $elems = $this->_listCount != null ? $this->_listCount : $defaultElems;
      $collection->getSelect()->limit($elems);

      //set order
      if ($this->_listOrder != null) {
        if ($this->_listOrder == 'rand') {
          $collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
        }
        else {
          $collection->getSelect()->order($this->_listOrder);
        }
      }
      
      $this->_productCollection = $collection->load();
    }

    return $this->_productCollection;
  }

  protected function _getCollectionType() {
    if (is_a($this->_category, 'Mage_Catalog_Model_Category') && $this->_category->getId() != null) {
      return self::_modeCategory;
    }
    elseif (!empty($filters)) {
      return self::_modeProducts;
    }
    else {
      throw new Mage_Core_Exception('Cannot detect listing mode. Provide category or attribute filters');
    }
  }
}
