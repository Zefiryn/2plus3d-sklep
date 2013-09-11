<?php

/**
 * Custom Address renderer
 */

class Zefiryn_SimpleCheckout_Block_Address_Renderer extends Mage_Customer_Block_Address_Renderer_Default {
  
  /**
   * Render address according to specified format
   * 
   * @param Mage_Customer_Model_Address_Abstract $address
   * @param string $format
   * @return string
   */
  public function render(Mage_Customer_Model_Address_Abstract $address, $format=null)
  {
    switch ($this->getType()->getCode()) {
      case 'simplecheckout_review':
        $dataFormat = Mage_Customer_Model_Attribute_Data::OUTPUT_FORMAT_HTML;
        break;
      default:
        $dataFormat = Mage_Customer_Model_Attribute_Data::OUTPUT_FORMAT_TEXT;
        break;
    }

    $formater   = new Varien_Filter_Template();
    $attributes = Mage::helper('customer/address')->getAttributes();

    $data = array();    
    foreach ($attributes as $attribute) {
      /* @var $attribute Mage_Customer_Model_Attribute */
      if (!$attribute->getIsVisible()) {
        continue;
      }
      if ($attribute->getAttributeCode() == 'country_id') {
        $data['country'] = $address->getCountryModel()->getName();
      } 
      else if ($attribute->getAttributeCode() == 'region') {        
        $data['region'] = Mage::helper('directory')->__($address->getRegion());
        $data['region_code'] = $this->_getRegionCode($address);
      } 
      else {
        $dataModel = Mage_Customer_Model_Attribute_Data::factory($attribute, $address);
        $value     = $dataModel->outputValue($dataFormat);
        if ($attribute->getFrontendInput() == 'multiline') {
          $values    = $dataModel->outputValue(Mage_Customer_Model_Attribute_Data::OUTPUT_FORMAT_ARRAY);
          // explode lines
          foreach ($values as $k => $v) {
            $key = sprintf('%s%d', $attribute->getAttributeCode(), $k + 1);
            $data[$key] = $v;
          }
        }
        $data[$attribute->getAttributeCode()] = $value;
      }
    }

    if ($this->getType()->getHtmlEscape()) {
      foreach ($data as $key => $value) {
        $data[$key] = $this->escapeHtml($value);
      }
    }

    $formater->setVariables($data);

    $format = !is_null($format) ? $format : $this->getFormat($address);

    return $formater->filter($format);
  }
  
  /**
   * Get region data
   * 
   * @see Zefiryn_SimpleCheckout_Helper_Data::getRegionCode()
   */
  protected function _getRegionCode(Mage_Customer_Model_Address_Abstract $address) {    
    return $this->helper('simplecheckout')->getRegionCode($address);
  }
}