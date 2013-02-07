<?php
class DpTD_Site_Block_Html_Footer extends Mage_Page_Block_Html_Footer {

  protected $_footerMenu;

  public function getFooterMenu() {
      if ($this->_footerMenu === null) {
      $this->_footerMenu = Mage::getModel('site/menu_item')->getFooterMenuTree();
    }
    
    return $this->_footerMenu;
  }
}