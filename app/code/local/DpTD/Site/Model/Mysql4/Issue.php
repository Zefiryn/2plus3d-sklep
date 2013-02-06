<?php
 
class DpTD_Site_Model_Mysql4_Issue extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('site/issue', 'id');
    }
}