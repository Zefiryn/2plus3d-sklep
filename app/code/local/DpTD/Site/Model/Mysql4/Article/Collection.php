<?php
 
class DpTD_Site_Model_Mysql4_Article_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
      $this->_init('site/article');
    }
}