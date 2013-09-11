<?php

class Zefiryn_SimpleCheckout_Block_Container extends Zefiryn_SimpleCheckout_Block_Abstract {
  
  /**
   * List checkout steps
   * 
   * @var array 
   */
  protected $_steps = array(
      'shipping' => 'step.shipping.info',
      'payment' => 'step.payment.info',
      'review' => 'step.review.info'
  );
  
  protected $_furtherCaption = array(
      'Next Up', 'Then'
  );
 
  /**
   * Get checkout steps
   * 
   * @return array
   */
  public function getSteps() {
    return $this->_steps;
  }  
  
  
  /**
   * Get step title
   * 
   * @param string $step
   * @return string $title
   */
  public function getStepTitle($step) {    
    $title = null;
    if (array_key_exists($step, $this->_steps)) {
      $title = $this->getChild($this->_steps[$step])->getStepTitle();
    }    
    return $title;
  }
  
  public function getPrevStepTitle($step) {    
    
    return $step == 'shipping' ? 'Shipping Address' : 'Payment Information';
  }
  
  /**
   * Get further steps info
   * 
   * @param string $step
   * @return array
   */
  public function getNextSteps($step) {
    $current = false;
    $furtherSteps = array();
    $i = 0;
    foreach($this->_steps as $_step => $block) {
      if ($_step == $step) {
        $current = true;
        continue;
      }
      if ($current == true) {
        $furtherSteps[$this->_furtherCaption[$i]] = $this->getChild($block)->getStepTitle();
        $i++;
      }
    }
    
    return $furtherSteps;
  }
  
  public function getPrevSteps($step) {
    $prevSteps = array();
    foreach($this->_steps as $_step => $block) {
      if ($_step == $step) {
        break;
      }
      $prevSteps[] = $_step;
    }
    return $prevSteps;
  }
  
  public function getCurrentStep() {
    return $this->helper('simplecheckout')->getSession()->getCurrentStep();
  }
  
  public function getReturnUrl() {    
    return $this->getUrl('checkout/' . $this->helper('simplecheckout')->getCheckoutType() . '/return', array('_secure' => $this->helper('simplecheckout')->isConnectionSecure()));
  }
}