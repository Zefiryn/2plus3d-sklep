<?php

class DpTD_Site_Block_Html_Topmenu extends Mage_Core_Block_Template {

  protected $_mainMenu;
  protected $_additionalElements = array();

  public function getMainMenu() {
    if ($this->_mainMenu === null) {
      $this->_mainMenu = Mage::getModel('site/menu_item')->getMainMenuTree();
      $this->_addShopMenuElements();
    }
    return $this->_mainMenu;
  }

  public function addMenuElement($alias, $title, $link, $prepare = true, $liParams = array()) {
    $element = Mage::getModel('site/menu_item');
    $element->setTitle($this->__($title));
    $element->setAdditional(true);
    $url = $prepare ? $this->getUrl($link) : $link;
    $element->setLink($url);
    foreach($liParams as $key => $val) {
      if ($key == 'class') {
        $element->setLiClass($val);
      }
    }
    
    $this->_additionalElements[$alias]['element'] = $element;
  }

  public function appendSubmenuBlock($blockName, $menuItem) {
    $this->_additionalElements[$menuItem]['submenuBlock'] = $blockName;
    return $this;
  }

  protected function _addShopMenuElements() {
    
    foreach($this->_additionalElements as $element) {
      $array['element'] = $element['element'];
      if (array_key_exists('submenuBlock', $element)) {
        $array['submenuBlock'] = $element['submenuBlock'];
      }
      $this->_mainMenu[] = $array;
    }    
    return $this;
  }


}