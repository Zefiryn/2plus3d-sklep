<?php
/**
 * Menu Item Paremeter Model
 *
 * @category   Menu
 * @package    DpTD_Menu
 * @author     Zefiryn <zefiryn@jewula.net>
 *
 */


class DpTD_Site_Model_Menu_Item_Parameter extends Mage_Core_Model_Abstract {

  public function _construct()
  {
    parent::_construct();
    $this->_init('site/menu_item_parameter');
  }
}