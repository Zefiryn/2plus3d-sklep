<?php

class Orba_Payupl_Model_Payment extends Mage_Payment_Model_Method_Abstract {

    protected $_code = 'payupl';
    protected $_formBlockType = 'payupl/form';
    protected $_isInitializeNeeded = true;
    protected $_canUseInternal = false;
    protected $_canUseForMultishipping = false;

    /* payu.pl payment state */

    const PAYMENTSTATE_NEW = '1';
    const PAYMENTSTATE_CANCELED = '2';
    const PAYMENTSTATE_DENIED = '3';
    const PAYMENTSTATE_INPROGRSSS = '4';
    const PAYMENTSTATE_PENDING = '5';
    const PAYMENTSTATE_REVERSED = '7';
    const PAYMENTSTATE_COMPLETED = '99';
    const PAYMENTSTATE_ERROR = '888';
    const POLISH_ZLOTY_CODE = 'PLN';

    /**
     * Get payupl session namespace
     *
     * @return Orba_Payupl_Model_Session
     */
    public function getSession() {
        return Mage::getSingleton('payupl/session');
    }

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout() {
        return Mage::getSingleton('checkout/session');
    }

    public function getOrderPlaceRedirectUrl() {
        return Mage::getUrl('payupl/payment/new', array('_secure' => true));
    }

    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote() {
        return $this->getCheckout()->getQuote();
    }

    public function getConfig() {
        return Mage::getModel('payupl/config');
    }

    private function getRedirectSig($data) {
        $md5Key = $this->getConfig()->getMD5Key1();
        $sig = md5(
                $data['pos_id'] .
                $data['session_id'] .
                $data['pos_auth_key'] .
                $data['amount'] .
                $data['desc'] .
                $data['order_id'] .
                $data['first_name'] .
                $data['last_name'] .
                $data['street'] .
                $data['city'] .
                $data['post_code'] .
                $data['email'] .
                $data['phone'] .
                $data['client_ip'] .
                $data['ts'] .
                $md5Key
        );
        return $sig;
    }

    public function getRedirectData($order) {
        $payment = $order->getPayment();
        $billing = $order->getBillingAddress();
        $order_id = $order->getId();
        $increment_order_id = $order->getRealOrderId();
        $rData = array(
            "pos_id" => $this->getConfig()->getPosId(),
            "pos_auth_key" => $this->getConfig()->getPosAuthKey(),
            "session_id" => $this->getSession()->getEncryptedSessionId() . '-' . $order_id,
            "amount" => $this->getOrderTotalInPolishCents($order),
            "desc" => Mage::helper('payupl')->__("Order no %s", $increment_order_id),
            "order_id" => $increment_order_id,
            "first_name" => $billing->getFirstname(),
            "last_name" => $billing->getLastname(),
            "street" => preg_replace("/\s/", " ", $billing->getStreetFull()),
            "city" => $billing->getCity(),
            "post_code" => $billing->getPostcode(),
            "email" => $order->getCustomerEmail(),
            "phone" => $billing->getTelephone(),
            "client_ip" => $_SERVER['REMOTE_ADDR'],
            "ts" => time() * rand(1, 10)
        );
        $rData['sig'] = $this->getRedirectSig($rData);
        return $rData;
    }

    //process payment update
    protected $_remotePaymentDataXML;
    protected $_order;

    public function processPaymentStateUpdate(array $request) {
        try {
            $this->_remotePaymentDataXML = $this->_postBack($request);
            if (!$this->_remotePaymentDataXML) {
                return false;
            }

            //remote payment info 
            $sessionId = (string) $this->_remotePaymentDataXML->trans[0]->session_id[0];
            $remoteState = (string) $this->_remotePaymentDataXML->trans[0]->status[0];
            $incrementOrderId = (string) $this->_remotePaymentDataXML->trans[0]->order_id[0];

            //local payment and order infor
            $this->_order = Mage::getModel('sales/order')->loadByIncrementId($incrementOrderId);
            $lPayment = $this->_order->getPayment();
            if ($lPayment == null) {
                throw new Exception('No payment exists for given order. ' . $request['session_id']);
            }

            if (!($this->isOrderCompleted($this->_order))) {
                $localState = $lPayment->getAdditionalInformation('payupl_state');
                $this->_onPaymentStateChange($lPayment, (string) $remoteState, (string) $localState);
            }
            return true;
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return false;
    }

    /* get payment state */
    protected function _postBack($data) {
        if (is_array($data) && isset($data['pos_id']) && isset($data['session_id'])) {
            $args = array(
                'pos_id' => $data['pos_id'],
                'session_id' => $data['session_id'],
                'ts' => now() * 1123
            );
            $md5Key = $this->getConfig()->getMD5Key1();
            $args['sig'] = md5($args['pos_id'] . $args['session_id'] . $args['ts'] . $md5Key);
            $url = $this->getConfig()->getGatewayUrl() . "/UTF/Payment/get/xml";

            try {
                $body = Mage::helper('payupl')->sendPost($url, $args);
                if (!$body) {
                    throw new Exception('Cannot connect to ' . $url);
                }
                return new Varien_Simplexml_Element($body);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        return false;
    }

    public function _onPaymentStateChange($pmnt, $newState, $oldState) {
        if ($newState != $oldState) {
            switch ($newState) {
                case Orba_Payupl_Model_Payment::PAYMENTSTATE_NEW: $this->_processPaymentChangeNew($pmnt);
                    break;
                case Orba_Payupl_Model_Payment::PAYMENTSTATE_CANCELED: $this->_processPaymentChangeCanceled($pmnt);
                    break;
                case Orba_Payupl_Model_Payment::PAYMENTSTATE_DENIED: $this->_processPaymentChangeDenied($pmnt);
                    break;
                case Orba_Payupl_Model_Payment::PAYMENTSTATE_INPROGRSSS: $this->_processPaymentChangeInProgress($pmnt);
                    break;
                case Orba_Payupl_Model_Payment::PAYMENTSTATE_PENDING: $this->_processPaymentChangePending($pmnt);
                    break;
                case Orba_Payupl_Model_Payment::PAYMENTSTATE_REVERSED: $this->_processPaymentChangeReversed($pmnt);
                    break;
                case Orba_Payupl_Model_Payment::PAYMENTSTATE_COMPLETED: $this->_processPaymentChangeCompleted($pmnt);
                    break;
                case Orba_Payupl_Model_Payment::PAYMENTSTATE_ERROR: $this->_processPaymenChangeError($pmnt);
                    break;
                default: $this->_processPaymentChangeError($pmnt);
                    break;
            }
            try {
                $pmnt->setAdditionalInformation('payupl_state', $newState);
                $pmnt->setAdditionalInformation('payupl_online', true);
                $pmnt->save();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    public function _processPaymentChangeNew($p) {
        $t = $p->setTransactionId((string) $this->_remotePaymentDataXML->trans[0]->session_id[0]);
        $t->setPreparedMessage('Nowa transakcja rozpoczęta. [' . Orba_Payupl_Model_Payment::PAYMENTSTATE_NEW . ']')
                ->save();
    }

    public function _processPaymentChangeCanceled($p) {
        $t = $p->setTransactionId((string) $this->_remotePaymentDataXML->trans[0]->session_id[0]);

        $t->setPreparedMessage('Transakcja anulowana. [' . Orba_Payupl_Model_Payment::PAYMENTSTATE_CANCELED . ']')
                ->registerVoidNotification()
                ->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID)
                ->save();

        // notify customer
        $comment = $this->_order->setState(Mage_Sales_Model_Order::STATE_HOLDED, true, 'Komunikat z Payu.pl: Transakcja anulowana.', true)
                ->sendOrderUpdateEmail()
                ->save();
    }

    public function _processPaymentChangeDenied($p) {
        $t = $p->setTransactionId((string) $this->_remotePaymentDataXML->trans[0]->session_id[0]);

        $t->setPreparedMessage('Transakcja odrzucona. [' . Orba_Payupl_Model_Payment::PAYMENTSTATE_DENIED . ']')
                ->setParentTransactionId((string) $this->_remotePaymentDataXML->trans[0]->session_id[0]) // this is the authorization transaction ID
                ->registerVoidNotification()
                ->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID)
                ->save();

        // notify customer
        $comment = $this->_order->setState(Mage_Sales_Model_Order::STATE_HOLDED, true, 'Komunikat z Payu.pl: Transakcja odrzucona.', true)
                ->sendOrderUpdateEmail()
                ->save();
    }

    public function _processPaymentChangeInProgress($p) {
        $t = $p->setTransactionId((string) $this->_remotePaymentDataXML->trans[0]->session_id[0]);

        $t->setPreparedMessage('Transakcja w trakcie realizacji. [' . Orba_Payupl_Model_Payment::PAYMENTSTATE_INPROGRSSS . ']')
                ->setTransactionId((string) $this->_remotePaymentDataXML->trans[0]->session_id[0]) // this is the authorization transaction ID
                ->registerVoidNotification()
                ->save();
    }

    public function _processPaymentChangePending($p) {
        $t = $p->setTransactionId((string) $this->_remotePaymentDataXML->trans[0]->session_id[0]);

        $p->setPreparedMessage('Transakcja oczekuje na zatwierdzenie. [' . Orba_Payupl_Model_Payment::PAYMENTSTATE_PENDING . ']')
                ->setParentTransactionId((string) $this->_remotePaymentDataXML->trans[0]->session_id[0]) // this is the authorization transaction ID
                ->registerVoidNotification()
                ->save();
    }

    public function _processPaymentChangeReversed($p) {
        $t = $p->setTransactionId((string) $this->_remotePaymentDataXML->trans[0]->session_id[0]);

        $t->setPreparedMessage('Transakcja zwrócona. [' . Orba_Payupl_Model_Payment::PAYMENTSTATE_REVERSED . ']')
                ->registerVoidNotification()
                ->save();

        // notify customer
        $comment = $this->_order->setState(Mage_Sales_Model_Order::STATE_HOLDED, true, 'Komunikat z Payu.pl: Transakcja zwrócona.', true)
                ->sendOrderUpdateEmail()
                ->save();
    }

    public function _processPaymentChangeCompleted($p) {
        $t = $p->setTransactionId((string) $this->_remotePaymentDataXML->trans[0]->session_id[0]);

        $t->setPreparedMessage('Transakcja zakończona pomyślnie. [' . Orba_Payupl_Model_Payment::PAYMENTSTATE_COMPLETED . ']')
                ->registerCaptureNotification($this->_remotePaymentDataXML->trans[0]->amount / 100)
                ->setIsTransactionApproved(true)
                ->setIsTransactionClosed(true)
                ->save();

        // notify customer
        $comment = $this->_order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Komunikat z Payu.pl: Transakcja zakończona pomyślnie.', true)
                ->sendOrderUpdateEmail(true, 'Komunikat z Payu.pl: Transakcja zakończona pomyślnie.')
                ->save();
    }

    public function _processPaymentChangeError($p) {
        $t = $p->setTransactionId((string) $this->_remotePaymentDataXML->trans[0]->session_id[0]);

        $p->setPreparedMessage('Transakcja błędna. [' . Orba_Payupl_Model_Payment::PAYMENTSTATE_ERROR . ']')
                ->setParentTransactionId((string) $this->_remotePaymentDataXML->trans[0]->session_id[0]) // this is the authorization transaction ID
                ->registerVoidNotification()
                ->save();

        // notify customer
        $comment = $this->_order->setState(Mage_Sales_Model_Order::STATE_HOLDED, true, 'Komunikat z Payu.pl: Transakcja błędna.')
                ->sendOrderUpdateEmail(true, 'Komunikat z Payu.pl: Transakcja błędna.')
                ->save();
    }

    public function isOrderCompleted($order) {
        $status = $order->getStatus();
        return in_array($status, array(Mage_Sales_Model_Order::STATE_CLOSED, Mage_Sales_Model_Order::STATE_CANCELED, Mage_Sales_Model_Order::STATE_COMPLETE));
    }

    protected function getOrderTotalInPolishCents($order) {
        if ($order->getBaseCurrencyCode() == self::POLISH_ZLOTY_CODE) {
            $total_pln = $order->getBaseGrandTotal();
        } else if ($order->getOrderCurrencyCode() == self::POLISH_ZLOTY_CODE) {
            $total_pln = $order->getGrandTotal();
        } else {
            $total_pln = Mage::helper('directory')->currencyConvert($order->getBaseGrandTotal(), $order->getBaseCurrencyCode(), self::POLISH_ZLOTY_CODE);
        }
        $rounded_pln = Mage::app()->getStore()->roundPrice($total_pln);
        return $rounded_pln * 100;
    }

}
