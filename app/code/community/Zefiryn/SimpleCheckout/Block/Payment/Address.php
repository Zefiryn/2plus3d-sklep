<?php

class Zefiryn_SimpleCheckout_Block_Payment_Address extends Mage_Checkout_Block_Onepage_Billing {
  
  public function getAddress() {
    if (is_null($this->_address)) {
      parent::getAddress();
    }
    
    //reset telephone
    if ($this->_address->getTelephone() == 'none') {
      $this->_address->setTelephone(null);
    }
    
    return $this->_address;
  }

  /**
   * Generate select box with addresses
   * 
   * @param string $type
   * @return string
   */
  public function getAddressesHtmlSelect($type) {
    if ($this->isCustomerLoggedIn()) {
      $options = array(array('value' => 'placeholder', 'label' => $this->__('Bill to a different address')));
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

      $jsObject = $type == 'billing' ? 'payment' : $type;
      $select = $this->getLayout()->createBlock('core/html_select')
              ->setName($type . '_address_id')
              ->setId($type . '-address-select')
              ->setClass('address-select')
              ->setExtraParams('onchange="' . $jsObject . '.newAddress(!this.value)"')
              ->setValue('placehoder')
              ->setOptions($options);

      $select->addOption('', Mage::helper('checkout')->__('New Address'));

      return $select->getHtml();
    }
    return '';
  }
  
  public function getSelectedBillingAddressId() {
    $addressId = $this->getAddress()->getCustomerAddressId();
    if (empty($addressId)) {
      $address = $this->getCustomer()->getPrimaryBillingAddress();
      if ($address) {
        $addressId = $address->getId();
      }
    }
    
    return $addressId;
    
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
    if (is_null($this->getAddress()->getCountryId())) {
      $select->setValue(Mage::helper('core')->getDefaultCountry());
    }
    return $select->getHtml();
  }
  
}