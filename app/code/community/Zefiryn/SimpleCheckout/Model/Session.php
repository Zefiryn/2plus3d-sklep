<?php
class Zefiryn_SimpleCheckout_Model_Session extends Mage_Core_Model_Session_Abstract {
  
  const STEP_STATUS_INCOMPLETE = 'incomplete';
  const STEP_STATUS_COMPLETE = 'completed';
  
  /**
   * Step information data
   * 
   * @var array
   */
  protected $_steps = array();
  
  public function __construct() {
    $this->init('simplecheckout');
    
    /*
     * retrieve data from session
     */
    $this->_steps = $this->getSessionSteps();
  }
  
  /**
   * Retrieve step information
   * 
   * @param string $step
   * @return array|Varien_Object
   */
  public function getStepData($step = null) {
    
    if ($step != null && array_key_exists($step, $this->_steps)) {
      return $this->_steps[$step];
    }
    elseif ($step != null && !array_key_exists($step, $this->_steps)) {
      return array();
    }
    elseif ($step == null) {
      return $this->_steps;
    }    
  }
  
  /**
   * Set step data
   * @param string $step
   * @param Varien_Object $data
   */
  public function setStepData($step, Varien_Object $data) {
    $this->_steps[$step] = $data;
    
    /*
     * this is required to save data in session
     */
    $this->setSessionSteps($this->_steps);
    return $this;
  }
  
  /**
   * Get first incomplete step
   * 
   * @return string
   */
  public function getCurrentStep() {
    foreach ($this->getStepData() as $step => $data) {
      if ($data->getStatus() == self::STEP_STATUS_INCOMPLETE) {
        return $step;
      }
    }
  }
  
  /**
   * Set active step
   * 
   * @param string $stepName
   */
  public function setStepComplete($stepName) {
    
    if ($data = $this->getStepData($stepName)) {
      $data->setStatus(self::STEP_STATUS_COMPLETE);
      $this->setStepData($stepName, $data);
    }
    else {
      throw new Exception(Mage::helper('core')->__("%s is not a valid step", $stepName));
    }
    
    return $this;
  }
  
  /**
   * Step steps as inactive after returning to previous step
   * @param type $stepName
   * @return \Zefiryn_SimpleCheckout_Model_Session
   */
  public function setActiveStep($stepName) {
    $prev = true;
    foreach($this->getStepData() as $step => $data) {
      
      //returning step or step after the one to which customer has returned
      if ($step == $stepName || !$prev) {
        $prev = false;
        $data->setStatus(self::STEP_STATUS_INCOMPLETE);
        $this->setStepData($step, $data);
      }
    }
    
    return $this;
  }
  
  /**
   * Check if a step is possible to proceed
   * 
   * @param string $stepName
   * @return boolean
   */
  public function canShowStep($stepName) {
 
    foreach($this->getStepData() as $step => $data) {
      if ($step != $stepName && $data->getStatus() == self::STEP_STATUS_INCOMPLETE) {
        return false;
      }
      elseif ($step == $stepName){
        return true;
      }
    }
    
    return true;
  }
}