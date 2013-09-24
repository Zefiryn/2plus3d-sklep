<?php

class DpTD_Checkout_Block_Invoice extends Mage_Core_Block_Template {

  public function __construct() {
    
  }
  
  protected function _beforeToHtml() {
    $order = Mage::registry('current_order');    
    if ($order && $order->getRequireInvoice() == DpTD_Checkout_Model_Quote_Attribute_InvoiceRequest::INVOICE_REQUEST_TRUE )
    {
      return '<br />'.$this->__('I want to receive the invoice.');
    }
      
  }
}