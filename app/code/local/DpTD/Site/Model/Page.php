<?php
/**
 * Pages Model
 *
 * @category   Menu
 * @package    DpTD_Menu
 * @author     Zefiryn <zefiryn@jewula.net>
 *
 */


class DpTD_Site_Model_Page extends Mage_Core_Model_Abstract {

  public function _construct()
  {
    parent::_construct();
    $this->_init('site/page');
  }
}