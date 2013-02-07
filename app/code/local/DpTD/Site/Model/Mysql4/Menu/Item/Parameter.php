<?php
 
class DpTD_Site_Model_Mysql4_Menu_Item_Parameter extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('site/menu_item_parameter', 'id');
    }
}