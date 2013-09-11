<?php

class Zefiryn_SimpleCheckout_Block_Payment extends Zefiryn_SimpleCheckout_Block_Abstract {
  
  /**
   * Url for ajax save action
   * @return string
   */
  public function getSaveUrl() {
    return $this->getUrl('checkout/main/savePayment' ,array ('_secure' => $this->helper('simplecheckout')->isConnectionSecure()));
  }
  
  public function getAddress() {
    if (is_null($this->_address)) {
      if ($this->_getQuote()->getBillingAddress()){ 
        $this->_address = $this->_getQuote()->getBillingAddress();
        
        if ($this->helper('simplecheckout')->isCustomerLoggedIn()) {
          if(!$this->_address->getFirstname()) {
            $this->_address->setFirstname($this->_getQuote()->getCustomer()->getFirstname());
          }
          if(!$this->_address->getLastname()) {
            $this->_address->setLastname($this->_getQuote()->getCustomer()->getLastname());
          }
        } 
      }
      else {
        $this->_address = Mage::getModel('sales/quote_address');
      }
    }
    return $this->_address;
  }  
  
  public function getQuoteBaseGrandTotal()
  {
    return (float)$this->_getQuote()->getBaseGrandTotal();
  }
  
  public function isCustomerBalanceUsed() {
    return $this->_getQuote()->getUseCustomerBalance();
  }
}

