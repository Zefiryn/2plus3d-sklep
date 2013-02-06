<?php

class DpTD_Site_Block_Html_Topmenu extends Mage_Core_Block_Template {

  protected $_mainMenu;
  protected $_footerMenu;

  public function getMainMenu() {
    if ($this->_mainMenu === null) {
      $this->_mainMenu = Mage::getModel('site/menu_item')->getMainMenuTree();
    }
    return $this->_mainMenu;
  }

}