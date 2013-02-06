<?php
/**
 * Issues Model
 *
 * @category   Site
 * @package    DpTD_Site
 * @author     Zefiryn <zefiryn@jewula.net>
 *
 */


class DpTD_Site_Model_Issue extends Mage_Core_Model_Abstract {

  protected $_coverImage;

  public function _construct()
  {
    parent::_construct();
    $this->_init('site/issue');
  }

  public function getCoverImage() {
    if($this->_coverImage === null) {
      $coverImage = Mage::getModel('site/image')->load($this->getCoverImageId());
      $this->_coverImage = $coverImage;
    }
    return $this->_coverImage;
  }
}