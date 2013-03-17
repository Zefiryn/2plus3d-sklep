<?php

class DpTD_Checkout_AjaxController extends Mage_Core_Controller_Front_Action {

  public function cartblockAction() {
    $request = $this->getRequest();
    if ($request->getParam('backlink', null)) {
      //add remove item return link
      $base = Mage::getBaseUrl();
      $link = str_replace($base, '/', urldecode($this->getRequest()->getParam('backlink',null)));
      Mage::log($link);
      $_SERVER['REQUEST_URI'] = $link;
    }      
    if ($request->isXMLHttpRequest()) {
      Mage::log($request->getServer('REQUEST_URI'));
      $this->loadLayout();
      $this->renderLayout();
    }
    else {
      $this->_redirect('');      
    }
  }

}