<?php
 
class DpTD_Site_Model_Mysql4_Menu_Item_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        //parent::__construct();
        $this->_init('site/menu_item');
    }
}