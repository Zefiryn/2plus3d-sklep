<?php
class Zefiryn_SimpleCheckout_Block_Shipping extends Mage_Checkout_Block_Onepage_Shipping {
  
  /**
   * Url for ajax save action
   * @return string
   */
  public function getSaveUrl() {
    return $this->getUrl('checkout/main/saveShipping', array('_secure' => $this->helper('simplecheckout')->isConnectionSecure()));
  }
  
  /**
   * Url for ajax calcualte price action
   * @return string
   */
  public function getShippingPriceUrl() {
    return $this->getUrl('checkout/main/shippingprice', array('_secure' => $this->helper('simplecheckout')->isConnectionSecure()));
  }
  
  public function getAddress() {
    if (is_null($this->_address)) {
      if ($this->getQuote()->getShippingAddress()){ 
        $this->_address = $this->getQuote()->getShippingAddress();
        
        if ($this->helper('simplecheckout')->isCustomerLoggedIn()) {
          if(!$this->_address->getFirstname()) {
            $this->_address->setFirstname($this->getQuote()->getCustomer()->getFirstname());
          }
          if(!$this->_address->getLastname()) {
            $this->_address->setLastname($this->getQuote()->getCustomer()->getLastname());
          }
        } 
      }
      else {
        $this->_address = Mage::getModel('sales/quote_address');
      }
    }
    
    //reset telephone
    if ($this->_address->getTelephone() == 'none') {
      $this->_address->setTelephone(null);
    }
    
    return $this->_address;
  }
  
  public function isStepCompleted($step) {
    return $this->helper('simplecheckout')->isStepCompleted($step);    
  }
  
  public function getCountryHtmlSelect($type) {
    $options = $this->getCountryOptions();
    $options[0]['label'] = $this->__('Country');
    $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type . '[country_id]')
            ->setId($type . ':country_id')
            ->setTitle(Mage::helper('checkout')->__('Country'))
            ->setClass('validate-select')
            ->setOptions($options);
    //if (is_null($this->getAddress()->getCountryId())) {
      $select->setValue(Mage::helper('core')->getDefaultCountry());
    //}
    return $select->getHtml();
  }
  
  public function getAddressesHtmlSelect($type) {
    if ($this->isCustomerLoggedIn()) {
      $options = array(array('value' => 'placeholder', 'label' => $this->__('Ship to a different address')));
      foreach ($this->getCustomer()->getAddresses() as $address) {
        $options[] = array(
            'value' => $address->getId(),
            'label' => $address->format('oneline')
        );
      }

      $addressId = $this->getAddress()->getCustomerAddressId();
      if (empty($addressId)) {
        if ($type == 'billing') {
          $address = $this->getCustomer()->getPrimaryBillingAddress();
        } else {
          $address = $this->getCustomer()->getPrimaryShippingAddress();
        }
        if ($address) {
          $addressId = $address->getId();
        }
      }

      $select = $this->getLayout()->createBlock('core/html_select')
              ->setName($type . '_address_id')
              ->setId($type . '-address-select')
              ->setClass('address-select')
              ->setExtraParams('onchange="' . $type . '.newAddress(!this.value)"')
              ->setValue('placeholder')
              ->setOptions($options);

      $select->addOption('', Mage::helper('checkout')->__('New Address'));

      return $select->getHtml();
    }
    return '';
  }
  
  public function getSelectedShippingAddressId() {
    $addressId = $this->getAddress()->getCustomerAddressId();
    if (empty($addressId)) {
      $address = $this->getCustomer()->getPrimaryShippingAddress();
      if ($address) {
        $addressId = $address->getId();
      }
    }
    
    return $addressId;
    
  }
}