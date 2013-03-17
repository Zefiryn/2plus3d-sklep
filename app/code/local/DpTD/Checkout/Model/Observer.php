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
  
  public function onAjaxRequest(Varien_Event_Observer $observer)  {
    
    $request = Mage::app()->getRequest();
    if ($request->getControllerName() == 'cart' && $request->getActionName() == 'add') {
      $is_ajax = $request->getParam('ajax',false);
      
      if ($is_ajax) {
        $messages = Mage::getSingleton('checkout/session')->getMessages(true);        
        $info = array('success' => true);
        foreach ($messages->getItems() as $message) {
          if ($message->getType() != 'success') {
            $info['success'] = false;
          }
          $info['info'][] = $message->getText();
        }
        
        $response = $observer->getEvent()->getResponse();      
        $response->clearHeaders();
        $response->setBody(Mage::helper('core')->jsonEncode($info));
        $response->setHttpResponseCode(200);
        $response->setHeader('Content-Type','application/json');
      }
    }
  }
}