<?php
class Zefiryn_SimpleCheckout_RController extends Zefiryn_SimpleCheckout_Controller_Abstract{
  
  
  public function shippingAction() {
    //always add default shipping address
    $address = $this->_getSession()->getQuote()->getShippingAddress();
    if (!$address->getStreet(-1) && !$address->getCity() && !$address->getPostcode()){
      $shipping = Mage::getSingleton('customer/session')->getCustomer()->getDefaultShippingAddress();
      if (!$shipping) {
        $addresses = Mage::getSingleton('customer/session')->getCustomer()->getAddresses();
        if (count($addresses) > 0) {
          $shipping = array_shift($addresses);        
        }
      }
      if ($shipping) {
        $address->importCustomerAddress($shipping);
      }
    }
    
    parent::shippingAction();
  }
}
