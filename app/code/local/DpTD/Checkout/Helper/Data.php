<?php 

class DpTD_Checkout_Helper_Data  extends Mage_Checkout_Helper_Data {

  public function getIsInvoiceRequested() {
    $checkout = Mage::getSingleton('checkout/session');
    
    return $checkout->getInvoiceRequest() == 1 ? 'true' : 'false';
  }

}