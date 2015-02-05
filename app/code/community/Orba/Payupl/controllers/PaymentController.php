<?php

class Orba_Payupl_PaymentController extends Mage_Core_Controller_Front_Action {
    
    protected $_session = null;
    protected $_order = null;
    protected $_payment = null;

    /* initiates new payment */
    public function newAction() {
        $this->setSession();
        $this->setOrder();
        $this->forceNewOrderStatus();
        $this->setPayment(true);
        $this->_order->sendNewOrderEmail();
        $this->loadLayout();
        $this->getLayout()->getBlock('payupl_child')->setOrder($this->_order);
        $this->renderLayout();
    }
    
    /* handles successful payment redirect */
    public function okAction() { 
        $this->setSession();
        $this->setOrder();
        $this->setPayment();
        if ($this->_payment->getAdditionalInformation('payupl_online') !== true && !Mage::getModel('payupl/payment')->isOrderCompleted($this->_order)) {
            $this->checkOrder($this->getPaymentSidFromResponse());
            if (defined('Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW')) {
                $this->_order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true)
                    ->save();
            } else {
                $this->_order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)
                    ->save();
            }
        }
        Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
        $this->_redirect('checkout/onepage/success', array('_secure'=>true));  
    }
    
    /* handles failed payment redirect*/
    public function errorAction() {
        $this->setSession();
        $this->setOrder();
        $this->setPayment();
        $this->checkOrder($this->getPaymentSidFromResponse());
        $params = $this->getRequest()->getParams();
        if (array_key_exists('code', $params)) {
            Mage::log('Error: Order id - '.$this->_order->getIncrementId().', Code - '.$params['code'], null, 'payupl.log');
        }
        if (!Mage::getModel('payupl/payment')->isOrderCompleted($this->_order)) {
            $this->_order->setState(Mage_Sales_Model_Order::STATE_HOLDED, true)
                 ->save();
        }
        Mage::getSingleton('checkout/session')->setErrorMessage($this->__('Your transaction was rejected by Payu.pl.'));
        Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
        $this->_redirect('checkout/onepage/failure', array('_secure'=>true));
    }
    
    /*handles payment status update - called by Payu.pl*/
    public function onlineAction() { 
        $error = false;
        $this->loadLayout();
        try {
            $data = $this->getRequest()->getPost();
            if (Mage::getModel('payupl/payment')->processPaymentStateUpdate($data)) {
                $this->getLayout()->getBlock('payupl_child')->setMessage('OK');
            } else {
                $error = true;
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $error = true;
        }
        if ($error) {
            $this->getLayout()->getBlock('payupl_child')->setMessage('ERROR');
        }
        $this->renderLayout();
    }
    
    private function setSession() {
        $this->_session = Mage::getSingleton('checkout/session');
        $this->_session->setQuoteId($this->_session->getPayuplQuoteId(true));
    }
    
    private function setOrder() {
        $id = $this->_session->getLastRealOrderId();
        $this->_order = Mage::getModel('sales/order')->loadByIncrementId($id);
    }
    
    private function getPaymentSidFromResponse() {
        $params = $this->getRequest()->getParams();
        $session = explode('-', $params['sid']);
        return $session[0];
    }
    
    private function checkOrder($payment_sid = null) {
        $customer_id = $this->_order->getData('customer_id');
        $session_customer_id = Mage::getSingleton('customer/session')->getData('id');
        $sid = Mage::getModel("core/session")->getEncryptedSessionId();
        if ($payment_sid !== null && $payment_sid != $sid) {
            Mage::throwException("Invalid session id.");
        }
        if ($session_customer_id == null) {
            if ($sid != $this->_payment->getAdditionalInformation('payupl_customer_sid')) {
                Mage::throwException("This is not this customer's order.");
            }
        } elseif ($customer_id != $session_customer_id) {
            Mage::throwException("This is not this customer's order.");
        }
    }
    
    private function setPayment($is_order_new = false) {
        $this->_payment = $this->_order->getPayment();
        if ($is_order_new) {
            $this->_payment->setAdditionalInformation('payupl_customer_sid', Mage::getModel("core/session")->getEncryptedSessionId());
            $this->_payment->save();
        }
    }
    
    private function isNewOrder() {
        return (Mage::getSingleton('checkout/session')->getLastRealOrderId() == $this->_order->getRealOrderId());
    }
    
    private function forceNewOrderStatus() {
        if ($this->isNewOrder()) {
            $status = $this->_order->getStatus();
            $state = $this->_order->getState();
            if ($state == Mage_Sales_Model_Order::STATE_NEW && $status != Mage::getStoreConfig("payment/payupl/order_status")) {
                $this->_order->setState(Mage::getStoreConfig("payment/payupl/order_status"), true)
                    ->save();
            }
        }
    }
	
}