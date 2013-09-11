<?php
/**
 * Link to simplecheckout in cart
 */
class Zefiryn_SimpleCheckout_Block_Link extends Mage_Core_Block_Template {

  /**
   * Url for chekcout link in cart
   * @return string
   */
  public function getCheckoutUrl()
  {
    if ($this->helper('simplecheckout')->isCustomerLoggedIn()) {
      //url for returning customer
      return $this->getUrl('checkout/r/shipping', array('_secure'=> true));
    }
    else if (Mage::getSingleton('checkout/type_onepage')->getCheckoutMethod() == Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER) {
      //url for new customer
      return $this->getUrl('checkout/f/shipping', array('_secure'=> $this->helper('simplecheckout')->isConnectionSecure()));
    }
  }
  
  /**
   * Url for ajax login popup
   * @return string
   */
  public function getLoginUrl()
  {
      return $this->getUrl('checkout/main/loginstep', array('_secure'=> $this->helper('simplecheckout')->isConnectionSecure()));
  }

  public function isDisabled()
  {
      return !Mage::getSingleton('checkout/session')->getQuote()->validateMinimumAmount();
  }

  public function isSimpleCheckoutPossible()
  {
      return $this->helper('simplecheckout')->canUseSimpleCheckout();
  }
}
