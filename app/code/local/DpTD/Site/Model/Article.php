<?php
/**
 * Issues Model
 *
 * @category   Site
 * @package    DpTD_Site
 * @author     Zefiryn <zefiryn@jewula.net>
 *
 */


class DpTD_Site_Model_Article extends Mage_Core_Model_Abstract {

  public function _construct()
  {
    parent::_construct();
    $this->_init('site/article');
  }

}