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
}