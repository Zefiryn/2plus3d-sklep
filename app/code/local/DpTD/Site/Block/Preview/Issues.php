<?php

class DpTD_Site_Block_Preview_Issues extends Mage_Core_Block_Template {

  protected $_issues;

  protected function _construct() {
    parent::_construct();
    $this->setTemplate('page/preview/issues.phtml');
  }

  public function getCollection() {
    if ($this->_issues == null) {
    $this->_issues = Mage::getModel('site/issue')->getCollection()->setOrder('number','ASC');
    }
    return $this->_issues;
  }

}