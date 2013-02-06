<?php
/**
 * Pages Model
 *
 * @category   Menu
 * @package    DpTD_Menu
 * @author     Zefiryn <zefiryn@jewula.net>
 *
 */


class DpTD_Site_Model_City extends Mage_Core_Model_Abstract {

  protected $_parent;

  public function _construct()
  {
    parent::_construct();
    $this->_init('site/city');
  }

  public function getParent() {
    if ($this->_parent == null) {
      $this->_parent = Mage::getModel('site/city')->load($this->getParentId());
    }

    return $this->_parent;
  }
}