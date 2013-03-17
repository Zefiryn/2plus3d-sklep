<?php

class DpTD_Checkout_AjaxController extends Mage_Core_Controller_Front_Action {

  public function cartblockAction() {
    if ($this->getRequest()->isXMLHttpRequest()) {
      $this->loadLayout();
      $this->renderLayout();
    }
    else {
      $this->_redirect('');
    }
  }

}