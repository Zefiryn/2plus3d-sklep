<?php

class DpTD_Checkout_Model_Observer {

  public function setInvoiceRequest(Varien_Event_Observer $observer) {
    $controller = $observer->getEvent()->getControllerAction();
    $request = $controller->getRequest();
    $data = $request->getPost('billing', array());
    $session = Mage::getSingleton('checkout/session');
    if (array_key_exists('require_invoice',$data)) {      
      $session->setInvoiceRequest(1);
      $session->getQuote()->setRequireInvoice('yes')->save();
    }
    else {
      $session->setInvoiceRequest(0);
      $session->getQuote()->setRequireInvoice('no')->save();
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
  
  public function addRequireInvoiceColumnToGrid(Varien_Event_Observer $observer) {
    
    $gridBlock = Mage::app()->getLayout()->getBlock('sales_order.grid');
    $gridBlock ->addColumn('require_invoice', array(
            'header' => Mage::helper('dptd_checkout')->__('Require Invoice'),
            'index' => 'require_invoice',
            'type'  => 'options',
            'width' => '70px',
            'options' => array( 'yes' => Mage::helper('dptd_checkout')->__('Yes'), 
                                'no' => Mage::helper('dptd_checkout')->__('No')),
        ));
    $gridBlock->addColumnsOrder('require_invoice', 'shipping_name');
  }
}