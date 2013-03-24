<?php
class DpTD_Site_Block_Html_Footer extends DpTD_Site_Block_Html_Abstract  {

  public function getFooterMenu() {
      if ($this->_menu === null) {
      $this->_menu = Mage::getModel('site/menu_item')->getFooterMenuTree();
      $this->_addShopMenuElements('footer');
    }
    
    return $this->_menu;
  }
}