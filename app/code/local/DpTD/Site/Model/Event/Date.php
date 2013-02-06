<?php
/**
 * Event_Date Model
 *
 * @category   Site
 * @package    DpTD_Site
 * @author     Zefiryn <zefiryn@jewula.net>
 *
 */


class DpTD_Site_Model_Event_Date extends Mage_Core_Model_Abstract {

  public function _construct()
  {
    parent::_construct();
    $this->_init('site/event_date');
  }
}