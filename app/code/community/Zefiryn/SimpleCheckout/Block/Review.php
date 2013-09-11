<?php

class Zefiryn_SimpleCheckout_Block_Review extends Mage_Checkout_Block_Onepage_Review_Info {

  protected function _construct() {
    parent::_construct();

    Mage::getSingleton('checkout/session')->getQuote()->collectTotals()->save();
  }

  /**
   * Url for ajax save action
   * @return string
   */
  public function getSaveUrl() {
    return $this->getUrl('checkout/main/placeOrder', array('_secure' => $this->helper('simplecheckout')->isConnectionSecure()));
  }

  public function getShippingAddressHtml() {
    return $this->helper('simplecheckout')->getQuote()->getShippingAddress()->format('simplecheckout_review');
  }

  public function getBillingAddressHtml() {
    return $this->helper('simplecheckout')->getQuote()->getBillingAddress()->format('simplecheckout_review');
  }

  public function getPaymentInfo() {
    $info = Mage::getSingleton('checkout/session')->getQuote()->getPayment();
    if ($info->getMethod()) {
      return $this->getLayout()
                  ->createBlock($info->getMethodInstance()->getInfoBlockType())
                  ->setInfo($info)
                  ->toHtml();
    }
    return false;
  }

  public function isStepCompleted($step) {
    return $this->helper('simplecheckout')->isStepCompleted($step);
  }
}
