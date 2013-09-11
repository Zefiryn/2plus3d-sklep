<?php

class Zefiryn_SimpleCheckout_Block_Review_Totals extends Mage_Core_Block_Template {
  
  /**
   * Tax configuration model
   * 
   * @var Mage_Tax_Model_Config
   */
  protected $_taxConfig;
  
  /**
   * Collect totals on construct
   */
  protected function _construct()
  {
    $this->helper('simplecheckout')->getQuote()->collectTotals()->save();
  }  
  
  /**
   * Get all totals
   * 
   * @return array
   */
  public function getTotals() {    
    return Mage::getSingleton('checkout/session')->getQuote()->getTotals();    
  }
  
  /**
   * Get grand total
   * 
   * @return Varien_Object
   */
  public function getGrandTotal() {
    $totals = $this->getTotals();
    return $totals['grand_total'];
  }
  
  /**
   * Get shipping total
   * 
   * @return Varien_Object
   */
  public function getShippingTotal() {
    $totals = $this->getTotals();
    if (array_key_exists('shipping', $totals)) {
      return $totals['shipping'];  
    }
    else {
      return new Varien_Object();
    }
    
  }
  
  /**
   * Get subtotal total
   * 
   * @return Varien_Object
   */
  public function getSubtotal() {
    $totals = $this->getTotals();
    return $totals['subtotal'];
  }
  
  /**
   * Get tax total info
   * 
   * @return Varien_Object
   */
  public function getTaxTotal() {
    $totals = $this->getTotals();
    return $totals['tax'];
  }
  
  /**
   * Calculate grand total excluding tax
   * 
   * @return float
   */
  public function getGrandTotalExcludingTax()
  {    
    $excl = $this->getGrandTotal()->getValue() - $this->getTaxTotal()->getValue();
    $excl = max($excl, 0);
    return $excl;
  }
  
  /**
   * Get tax configuration model
   * 
   * @return Mage_Tax_Model_Config
   */
  protected function _getTaxConfig() {
    if ($this->_taxConfig === null ){
      $this->_taxConfig = Mage::getSingleton('tax/config');    
    }
    
    return $this->_taxConfig;
  }
}

