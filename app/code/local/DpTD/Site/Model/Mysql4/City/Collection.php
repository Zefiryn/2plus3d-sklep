<?php
 
class DpTD_Site_Model_Mysql4_City_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
  public function _construct()
  {
    $this->_init('site/city');
  }

  protected function _beforeLoad() {
    $this->addFieldToFilter('type', array('eq' => 'city'));
    return parent::_beforeLoad();
  }
}