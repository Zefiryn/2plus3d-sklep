<?php
/**
 * Basic helper class for SimpleCheckout module
 */
class Zefiryn_SimpleCheckout_Helper_Data extends Mage_Core_Helper_Abstract {  
  
  /**
   * Config XPATH
   */
  const XML_PATH_GRANDTOTAL_TAX_CONFIG = 'simplecheckout/options/grandtotal_tax';
  const XML_PATH_MODULE_ENABLED = 'simplecheckout/options/simplecheckout_enabled';
  
  /**
   * Retrieve shopping cart model object
   * 
   * @return Mage_Checkout_Model_Cart
   */    
  public function getCart()
  {
    return Mage::getSingleton('checkout/cart');
  }

  /**
   *  Get current active quote instance
   * 
   *  @return Mage_Sales_Model_Quote
   */
  public function getQuote()
  {
    return Mage::getSingleton('checkout/session')->getQuote();
  }
  
  /**
   *  Get simplecheckout session object
   * 
   *  @return WSYNC_Simplecheckout_Model_Session
   */
  public function getSession()
  {
    return Mage::getSingleton('simplecheckout/session');
  }
   
  /**
   * Get simplecheckout availability
   * 
   * @return bool
   */
  public function canUseSimpleCheckout()
  {
       return (bool)Mage::getStoreConfig(self::XML_PATH_MODULE_ENABLED);
  }
  
  /**
   * Check if customer is logged in
   * 
   * @return boolean
   */
  public function isCustomerLoggedIn()
  {
    return Mage::getSingleton('customer/session')->isLoggedIn();
  }
  
  /**
   * Get customer current object
   * 
   * @return Mage_Customer_Model_Customer
   */
  public function getCustomer()
  {
    return Mage::getSingleton('customer/session')->getCustomer();
  }
  
  /**
   * Check if billing and shipping addresses are the same
   * 
   * @return boolean
   */
  public function isBillingSameAsShipping() {
    $billing = $this->getQuote()->getBillingAddress();
    $street = $billing->getStreetFull();
    $flag = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getSameAsBilling()
              || Mage::getSingleton('simplecheckout/session')->getBillingSameAsShipping()
              || (empty($street) && !$billing->getCity() && !$billing->getPostcode());
    return $flag;
  }
  
  /**
   * Get grand total tax config option
   * 
   * @return boolean
   */
  public function showGrandTotalWithTax() {
    return (bool)Mage::getStoreConfig(self::XML_PATH_GRANDTOTAL_TAX_CONFIG);
  }
  
  /**
   * Success page url
   * 
   * @return string
   */
  public function getSuccessPageUrl() {
    return Mage::getUrl('checkout/main/success');
  }
  
  /**
   * Check if given step is completed
   * 
   * @param string $step
   * @return boolean
   */
  public function isStepCompleted($step) {
    return Mage::getSingleton('simplecheckout/session')->getStepData($step)->getStatus();
  }
  
  /**
   * Check if connection is secure
   * 
   * @return boolean
   */
  public function isConnectionSecure() {
    return $_SERVER['SERVER_PORT'] == '443';
  }
  
  /**
   * Get region data
   * 
   * @param Mage_Customer_Model_Address_Abstract $address
   * @return string
   */
  public function getRegionCode(Mage_Customer_Model_Address_Abstract $address) {
    
    $region = Mage::getModel('directory/region')->load($address->getRegionId());
    return $region->getCode() != null ? $region->getCode() : $region->getName();
  }
  
  /**
   * Get checkout type (r for returning customer, f for first time customer)
   * 
   * @return string
   */
  public function getCheckoutType() {
    return Mage::helper('simplecheckout')->isCustomerLoggedIn() ? 'r' : 'f';
    
  }
}