<?php
 
class DpTD_Site_Model_Mysql4_Image_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
      $this->_init('site/image');
    }
}