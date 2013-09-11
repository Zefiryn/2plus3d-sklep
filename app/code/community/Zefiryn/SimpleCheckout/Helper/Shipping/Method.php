<?php

class Zefiryn_SimpleCheckout_Helper_Shipping_Method extends Zefiryn_SimpleCheckout_Helper_Data {

  protected $_rates;
  protected $_address;

  /**
   * Collect shipping rates
   * 
   * @return array
   */
  public function getShippingRates() {

    if (empty($this->_rates)) {
      $this->getAddress()->collectShippingRates()->save();

      $groups = $this->getAddress()->getGroupedAllShippingRates();
      return $this->_rates = $groups;
    }

    return $this->_rates;
  }

  /**
   * Gete shipping address
   * 
   * @return Mage_Sales_Model_Quote_Address
   */
  public function getAddress() {
    if (empty($this->_address)) {
      $this->_address = $this->getQuote()->getShippingAddress();
    }
    return $this->_address;
  }

  /**
   * Get Carrier Name from configuration
   * 
   * @param string  $carrierCode
   * @return string
   */
  public function getCarrierName($carrierCode) {
    if ($name = Mage::getStoreConfig('carriers/' . $carrierCode . '/title')) {
      return $name;
    }
    return $carrierCode;
  }

  /**
   * Get selected shipping method
   * 
   * @return string
   */
  public function getAddressShippingMethod() {
    return $this->getAddress()->getShippingMethod();
  }

  /**
   * 
   * @param double $price
   * @param boolean $flag
   * @return double
   */
  public function getShippingPrice($price, $flag) {
    return $this->getQuote()->getStore()->convertPrice(Mage::helper('tax')->getShippingPrice($price, $flag, $this->getAddress()), true);
  }

}
