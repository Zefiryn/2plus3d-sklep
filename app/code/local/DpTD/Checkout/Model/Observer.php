<?php

class DpTD_Checkout_Model_Observer {

  public function setInvoiceRequest(Varien_Event_Observer $observer) {
    $controller = $observer->getEvent()->getControllerAction();
    $request = $controller->getRequest();
    $data = $request->getPost('billing', array());
    $session = Mage::getSingleton('checkout/session');
    if (array_key_exists('require_invoice',$data)) {      
      $session->setInvoiceRequest(1);
    }
    else {
      $session->setInvoiceRequest(0);
    }
    
    return $this;
  }
}