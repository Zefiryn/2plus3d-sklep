<?php
/**
 * Issues Model
 *
 * @category   Site
 * @package    DpTD_Site
 * @author     Zefiryn <zefiryn@jewula.net>
 *
 */


class DpTD_Site_Model_Image extends Mage_Core_Model_Abstract {

  public function _construct()
  {
    parent::_construct();
    $this->_init('site/image');
  }

  public function getUrl($type = '_original')
  {
    if($type == '_original') {
      $type = '';
    }
    
    $url = Mage::helper('site')->getSiteBaseUrl() . 'assets/images/' . $this->getId();
    if(!empty($type)) {
      $url .= "_$type";
    }
    $url .= '_' . rawurlencode($this->getName());
    return $url;
  }
}