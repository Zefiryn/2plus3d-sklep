<?php 

class DpTD_Checkout_Helper_Data  extends Mage_Checkout_Helper_Data {

  public function getIsInvoiceRequested() {
    $checkout = Mage::getSingleton('checkout/session');
    
    return $checkout->getQuote()->setRequireInvoice() == DpTD_Checkout_Model_Quote_Attribute_InvoiceRequest::INVOICE_REQUEST_TRUE ? 'true' : 'false';
  }

}