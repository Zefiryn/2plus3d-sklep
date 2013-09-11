<?php

/**
 * Abstract class for all checkout blocks
 */

class Zefiryn_SimpleCheckout_Block_Abstract extends Mage_Core_Block_Template {
  
  /**
   * Retrieve shopping cart model object
   * 
   * @return Mage_Checkout_Model_Cart
   */    
  protected function _getCart()
  {
    return Mage::getSingleton('checkout/cart');
  }
  
  /**
   *  Get current active quote instance
   * 
   *  @return Mage_Sales_Model_Quote
   */
  protected function _getQuote()
  {
    return $this->_getCart()->getQuote();
  }
   
  /**
   * Check if given step is completed
   * 
   * @param string $step
   * @return boolean
   */
  public function isStepCompleted($step) {
    return $this->helper('simplecheckout')->isStepCompleted($step);    
  }
  
}
