<?php

class DpTD_Site_Block_Html_Abstract extends Mage_Core_Block_Template {

  protected $_menu;
  protected $_additionalElements = array();
  
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
  
  public function addSublevelElement($menuItem, $label, $link, $title, $prepare = 1, $position = null, $liParams = array()) {
    $element = Mage::getModel('site/menu_item');
    $element->setTitle($this->__($label));
    $element->setAdditional(true);
    $url = $prepare ? $this->getUrl($link) : $link;
    $element->setLink($url);
    foreach($liParams as $key => $val) {
      if ($key == 'class') {
        $element->setLiClass($val);
      }
    }
    $liPos = $position == null ? count($this->_additionalElements[$menuItem]['sublevel']) : $position; 
    $this->_additionalElements[$menuItem]['sublevel'][$liPos] = $element;
    return $this;
  }
  
  protected function _addShopMenuElements($type) {
    
    $arrayTypeName = $type == 'main' ? 'submenuBlock' : 'sublevel';
    foreach($this->_additionalElements as $element) {
      $array['element'] = $element['element'];
      if (array_key_exists('submenuBlock', $element)) {
        $array[$arrayTypeName] = $element['submenuBlock'];
      }
      elseif (array_key_exists('sublevel', $element)) {
        $array[$arrayTypeName] = $element['sublevel'];
      }
      $this->_menu[] = $array;
    }    
    return $this;
  }
}