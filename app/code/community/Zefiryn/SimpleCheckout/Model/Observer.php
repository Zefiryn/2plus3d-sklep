<?php

class Zefiryn_SimpleCheckout_Model_Observer {

  /**
   * Change redirect response to json response after logging in 
   * when it has been done with our login step form
   * 
   * @param Varien_Event_Observer $observer
   * @return \Zefiryn_SimpleCheckout_Model_Observer
   */
  public function accountPostResponse(Varien_Event_Observer $observer) {

    $controller = $observer->getEvent()->getControllerAction();

    if ($controller->getRequest()->isXmlHttpRequest()) {
      $response['error'] = 0;
      $session = Mage::getSingleton('customer/session');
      $messages = $session->getMessages(true)->getItems('error');
      if ($messages != null){
        foreach($messages as $type => $message) {                    
          $response['error'] = 1;
          $response['message'][] = $message->getCode();
        }
      }
      else {
        Mage::helper('simplecheckout')->getSession()->setStepComplete('login');
        $response['error'] = 0;
        $response['redirect'] = Mage::getModel('core/url')->getUrl('checkout/r/shipping', array('_secure' => true));
      }
      $controller->getResponse()
              ->clearAllHeaders()
              ->setHeader('Content-Type', 'application/json')
              ->setHttpResponseCode(200)
              ->setBody(Mage::helper('core')->jsonEncode($response));
    }
    
    return $this;
  }
  
  /**
   * Change redirect response to json response after sending password reset link  
   * when it has been done with our login step form
   * 
   * @param Varien_Event_Observer $observer
   * @return \Zefiryn_SimpleCheckout_Model_Observer
   */
  public function forgotpasswordPostResponse(Varien_Event_Observer $observer) {

    $controller = $observer->getEvent()->getControllerAction();

    if ($controller->getRequest()->isXmlHttpRequest()) {
      $response['error'] = 0;
      $session = Mage::getSingleton('customer/session');
      $error_messages = $session->getMessages(false)->getItems('error');
      $success_messages = $session->getMessages(true)->getItems('success');
      
      if ($error_messages != null){
        foreach($error_messages as $type => $message) {                    
          $response['error'] = 1;
          $response['message'][] = $message->getCode();
        }
      }
      else {
        $response['error'] = 0;
        $response['show_step'] = 'login';
        if ($success_messages ){
        foreach($success_messages as $type => $message) {                    
          $response['message'][] = $message->getCode();
        }
      }
      }
      $controller->getResponse()
              ->clearAllHeaders()
              ->setHeader('Content-Type', 'application/json')
              ->setHttpResponseCode(200)
              ->setBody(Mage::helper('core')->jsonEncode($response));
    }
    
    return $this;
  }
  
  /**
   * Prepare information for review summary
   * 
   * @param Varien_Event_Observer $observer
   * @return \Zefiryn_SimpleCheckout_Model_Observer
   */
  public function prepareCCInfo(Varien_Event_Observer $observer) {
    $payment = $observer->getEvent()->getPayment();
    $transport = $observer->getEvent()->getTransport();    
    if ($payment->getMethod() == 'authorizenet') {
      $transport[Mage::helper('payment')->__('Credit Card Number')] = sprintf('xxxx xxxx xxxx %s', $payment->getCcLast4());
      $transport[Mage::helper('payment')->__('Expires')] = sprintf('Expires: %s/%s', $payment->getData('cc_exp_month'), $payment->getData('cc_exp_year'));
    }
    return $this;
    
  }
  
  /**
   * Reset CC data if changing to no credit card payment type
   * 
   * @param Varien_Event_Observer $observer
   * @return \Zefiryn_SimpleCheckout_Model_Observer
   */
  public function setPaymentData(Varien_Event_Observer $observer) {
    $data = $observer->getEvent()->getInput();
    if ($data->getCcNumber() == null) {
      $data->setCcNumber(null);
      $data->setCid(null);
      $data->setCcExpMonth(null);
      $data->setCcExpYear(null);
      $data->setCcLast4(null);
      $data->setCcType(null);
    }
    return $this;
  }

}
