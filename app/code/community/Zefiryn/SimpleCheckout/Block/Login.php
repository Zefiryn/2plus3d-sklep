<?php

class Zefiryn_SimpleCheckout_Block_Login extends Zefiryn_SimpleCheckout_Block_Abstract {

  public function getStoreName() {
    return Mage::getStoreConfig('design/head/default_title');
  }

  public function getFormData() {

    $data = $this->getData('form_data');
    if (is_null($data)) {
      $formData = Mage::getSingleton('customer/session')->getCustomerFormData(true);
      $data = new Varien_Object();
      if ($formData) {
        $data->addData($formData);
        $data->setCustomerData(1);
      }
      if (isset($data['region_id'])) {
        $data['region_id'] = (int) $data['region_id'];
      }
      $this->setData('form_data', $data);
    }
    return $data;
  }

  /**
   *  Newsletter module availability
   *
   *  @return boolean
   */
  public function isNewsletterEnabled() {
    return Mage::helper('core')->isModuleOutputEnabled('Mage_Newsletter');
  }
  
  /**
   * Url for login action
   * @return string
   */
  public function getPostActionUrl() {
    return $this->getUrl('customer/account/loginPost', array('_secure'=>$this->helper('simplecheckout')->isConnectionSecure()));
  }
  
  /**
   * Url for registering new customer
   * @return string
   */
  public function getRegisterPostActionUrl() {
    return $this->getUrl('checkout/main/registerCustomer', array('_secure'=>$this->helper('simplecheckout')->isConnectionSecure()));
  }
  
  /**
   * Url for reseting password action
   * @return string
   */
  public function getForgotPasswordActionUrl() {
    return $this->getUrl('customer/account/forgotpasswordpost', array('_secure'=>$this->helper('simplecheckout')->isConnectionSecure()));
  }

}
