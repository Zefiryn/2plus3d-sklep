<?php
class DpTD_Catalog_Helper_Data extends Mage_Catalog_Helper_Data
{
  public function getFormatedDate($date) {
    return Mage::helper('site')->ago($date);
  }
}