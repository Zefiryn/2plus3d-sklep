<?php

class DpTD_Site_Block_Html_Topmenu extends DpTD_Site_Block_Html_Abstract {

  protected $_additionalElements = array();

  public function getMainMenu() {
    if ($this->_menu === null) {
      $this->_menu = Mage::getModel('site/menu_item')->getMainMenuTree();
      $this->_addShopMenuElements('main');
    }
    return $this->_menu;
  }
  
  public function addCategoriesBLock($menuItem, $level = 1) {
    $rootcatId= Mage::app()->getStore()->getRootCategoryId(); 
    $categories = Mage::getModel('catalog/category')->getCategories($rootcatId, $level, true, true);
    
    /**
     * @var Mage_Page_Block_Template_Links $block
     */
    $block = $this->getLayout()->createBlock('page/template_links', 'categories_links', array('template'=> "page/template/links.phtml"));
    foreach($categories as $category) {
      $block->addLink($category->getName(), $category->getUrl(), $category->getName());
    }

    $this->appendSubmenuBlock($block, $menuItem);
    return $this;
  }
  
}