<?php

class Zefiryn_SimpleCheckout_Controller_Abstract extends Mage_Core_Controller_Front_Action {
  
  /**
   * Block renderer functions
   * 
   * @var array
   */
  protected $_sectionUpdateFunctions = array(
      'payment-method' => '_renderPaymentBlock',
      'review' => '_renderReviewBlock',
  );

  /**
   * List of available steps
   * 
   * @var array
   */
  protected $_steps = array('login', 'shipping', 'payment', 'review');

  /**
   * Prepare basic step information
   */
  public function preDispatch() {
    parent::preDispatch();
    
    /**
     * init steps information and store it in the session object
     */
    if (!$this->_isCheckoutStarted()) {
      foreach ($this->_steps as $step) {
        $data = new Varien_Object(array('status' => 'incomplete'));
        if ($step == 'login' && (Mage::helper('simplecheckout')->isCustomerLoggedIn() || $this->getOnepage()->getCheckoutMethod() == Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER)) {
          $data->setStatus('completed');
        }
        $this->_getSimpleSession()->setStepData($step, $data);
      }
    }
    
    return $this;   
  }
  
  /**
   * Check if this is ajax request
   * 
   * @return boolean
   */
  protected function _checkAjaxRequest() {
    return $this->getRequest()->isXMLHttpREquest();
  }

  /**
   * Get one page checkout model
   * 
   * @return Mage_Checkout_Model_Type_Onepage
   */
  public function getOnepage() {
    return Mage::getSingleton('checkout/type_onepage');
  }

  /**
   * Retrieve checkout session object
   * 
   * @return Mage_Checkout_Model_Session
   */
  protected function _getSession() {
    return Mage::getSingleton('checkout/session');
  }

  /**
   * Retrieve simplecheckout session object
   * 
   * @return Mage_Checkout_Model_Session
   */
  protected function _getSimpleSession() {
    return Mage::getSingleton('simplecheckout/session');
  }

  /**
   * Check if user can perform checkout
   * 
   * @return boolean
   */
  protected function _canCheckout() {
    $items = $this->getOnepage()->getQuote()->hasItems();
    $checkoutSetup = (Mage::helper('simplecheckout')->isCustomerLoggedIn() || $this->getOnepage()->getCheckoutMethod() == Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER);
    
    if ($items && !$checkoutSetup) {
      $this->_getSession()->addError('Please login or create an account before going to the checkout');
    }
    
    if ($items && $checkoutSetup) {
      return true;
    }
    
    return false;
  }

  /**
   * Request login/register form for the first step
   */
  public function loginstepAction() {
    if ($this->_checkAjaxRequest()) {
      $layout = $this->getLayout();
      $update = $layout->getUpdate();
      $update->load('simplecheckout_step_login');
      $layout->generateXml();
      $layout->generateBlocks();
      $output = $layout->getOutput();
      $response['html'] = $output;
      $this->_sendAjaxResponse($response);
    } 
    else {
      $this->_redirect('checkout/cart');
    }
  }


  /**
   * Set checkout method to register
   */
  public function registerCustomerAction() {
    
    if ($this->_checkAjaxRequest()) {
      
      $customerResult = $this->_validateNewCustomer();
      if (!isset($customerResult['error'])) {
        
        $result = $this->getOnepage()->saveCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER);
        $data = $this->getRequest()->getPost();
        $data['confirm_password'] = isset($data['confirm_password']) ?  $data['confirm_password'] : $data['customer_password'];
        $this->_getSimpleSession()->setUserData($data);
        if (!isset($result['error'])) {
          $this->_setStepComplete('login');
          $response['error'] = 0;
          $response['redirect'] = Mage::getUrl('checkout/f/shipping', array('_secure' => true));
        } else {
          $response = $result;
        }
      }
      else {
        $response = $customerResult;
      }
      
      $this->_sendAjaxResponse($response);      
    } 
    else {      
      $this->_redirect('checkout/cart');
    }
  }

  /**
   * Main checkout page
   */
  public function indexAction() {

    if ($this->_canCheckout()) {
      $this->loadLayout();
      $this->renderLayout();
    } else {
      //$this->_getSession()->addError('Your cart is empty and you have nothing to checkout.');
      $this->_redirect('checkout/cart');
    }
  }

  /**
   * Save shipping address action
   */
  public function saveShippingAction() {

    if ($this->_checkAjaxRequest() && $this->getRequest()->isPost()) {

      $data = $this->_getShippingAddress();
      //set telephone if there is none
      $data['telephone'] = $data['telephone'] == null ? 'none' : $data['telephone'];
      $newCustomerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
      $savedCustomerAddressId = $this->getRequest()->getPost('selected_shipping_address_id', false);      
      $customerAddressId = $newCustomerAddressId != 'placeholder' ? $newCustomerAddressId : $savedCustomerAddressId;
      $data['shipping_address_id'] = $customerAddressId;
      $this->_getSimpleSession()->setShippingData($data);
      $result = $this->getOnepage()->saveShipping($data, $customerAddressId);      

      if (!isset($result['error'])) {
        
        $shippingMethodResult = $this->_saveShippingMethod();
        
        if (!isset($shippingMethodResult['error'])) {
          $this->_setStepComplete('shipping');
          $response = array('goto_section' => 'payment',
                            'navigator_state' => $this->_getNavigatorState('payment'),
                            'section_html' => array('shipping' =>  $this->_renderStepBlock('shipping'),
                                                     'payment' =>  $this->_renderStepBlock('payment')
                            )
                      );
        }
        else {
          $response = $result;
        }        
      } 
      else {
        $response = $result;
      }

      $this->_sendAjaxResponse($response);
    } else {
      $this->_redirect('*');
    }
  }
  
  /**
   * Select cheapest shipping method and save
   * 
   * @return array
   */
  protected function _saveShippingMethod() {
    $rates = $this->_getShipppingRates();
    $cheapestMethod = array_keys($rates['prices'],min($rates['prices']));
    $result = $this->getOnepage()->saveShippingMethod($rates['methods'][$cheapestMethod[0]]);
    if (!$result) {      
      Mage::dispatchEvent(
              'checkout_controller_onepage_save_shipping_method',
              array('request' => $this->getRequest(),
                    'quote'   => $this->getOnepage()->getQuote()));
      
      $this->getOnepage()->getQuote()->collectTotals()->save();
    }
            
    return $result;
  } 
  
  /**
   * Get shipping rates for the current shipping address
   * 
   * @return array
   */
  protected function _getShipppingRates() {
    $_helper = Mage::helper('simplecheckout/shipping_method');
    $_shippingRateGroups  = $_helper->getShippingRates();
    $result  = array();
    foreach ($_shippingRateGroups as $_rates) {
      foreach ($_rates as $_rate) {
        $methodCode = 's_method_'. $_rate->getCode();
        $result['prices'][$methodCode] = (float)$_rate->getPrice();
        $result['methods'][$methodCode] = $_rate->getCode();
      }
    }
    
    return $result;
  }
  
  /**
   * Calculate shipping price for ajax request
   */
  public function shippingpriceAction() {
    if ($this->_checkAjaxRequest() && $this->getRequest()->isPost()) {
      $quote = Mage::getSingleton('checkout/session')->getQuote();            
      $shippingAddressId = $this->getRequest()->getPost('shipping_address_id', false);
      
      if ($shippingAddressId) {
        //get address values from database
        $customerAddress = Mage::getModel('customer/address')->load($shippingAddressId);
        $data = $customerAddress->getData();
      }
      else {
        $data = $this->getRequest()->getPost('shipping');
      }
      
      $savedData = $quote->getShippingAddress()->getData();
      $quote->getShippingAddress()
				->setCountryId($data['country_id'])
				->setCity($data['city'])
				->setPostcode($data['postcode'])
				->setRegionId(isset($data['region_id']) ? $data['region_id'] : 0)
				->setRegion(isset($data['region']) ? $data['region'] : null)
				->setCollectShippingRates(true)
                ->collectTotals();
      $groups = $quote->getShippingAddress()->getGroupedAllShippingRates();
      $result['prices'] = array();
      foreach ($groups as $_rates) {
        foreach ($_rates as $_rate) {
          $methodCode = 's_method_'. $_rate->getCode();
          $result['prices'][$methodCode] = (float)$_rate->getPrice();          
        }
      }
      $cheapestMethod = array_keys($result['prices'],min($result['prices']));      
      $return['country'] = Mage::getModel('directory/country')->load($data['country_id'])->getName();
      $return['price'] = Mage::helper('core')->currency($result['prices'][$cheapestMethod[0]], true, false);
      
      //restore previous address data
      $quote->getShippingAddress()
            ->setCountryId($savedData['country_id'])
            ->setCity($savedData['city'])
            ->setPostcode($savedData['postcode'])
            ->setRegionId($savedData['region_id'])
            ->setRegion($savedData['region'])
            ->save();
      
      $this->_sendAjaxResponse($return);
    }
    else {
      $this->_redirect('checkout/cart');
    }
  }

  /**
   * Save payment action
   */
  public function savePaymentAction() {
    if ($this->_checkAjaxRequest() && $this->getRequest()->isPost()) {

      try {
        $this->_saveBillingData();
        $this->_checkPaymentMethod();
        $data = $this->getRequest()->getPost('payment', array());
        //recollect totals after applying customer balance
        $this->getOnepage()->getQuote()->setTotalsCollectedFlag(false);
        $result = $this->getOnepage()->savePayment($data);        

        if (!isset($result['error'])) {          
          $this->_setStepComplete('payment');
          $response = array('goto_section' => 'review',
                            'navigator_state' => $this->_getNavigatorState('review'),
                            'section_html' => array('review' => $this->_renderStepBlock('review'))
                      );
        } else {
          $response = $result;
          ;
        }
      } catch (Exception $e) {
        $response['error'] = true;
        $response['message'] = $e->getMessage();
      }

      $this->_sendAjaxResponse($response);
    } else {
      $this->_redirect('*');
    }
  }

  /**
   * Save order
   */
  public function placeOrderAction() {

    if ($this->_checkAjaxRequest() && $this->getRequest()->isPost()) {
      try {
        $data = $this->getRequest()->getPost('payment', array());
        if ($data) {
          $this->getOnepage()->getQuote()->getPayment()->importData($data);
        }

        $this->getOnepage()->saveOrder();

        $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
        $response['success'] = true;
        $response['redirect'] = Mage::helper('simplecheckout')->getSuccessPageUrl();
        $this->_setStepComplete('review');
      } 
      catch (Mage_Payment_Model_Info_Exception $e) {
        
        $message = $e->getMessage();
        if (!empty($message)) {
          $response['message'] = $message;
        }
        $response['error'] = true;
        $response['goto_section'] = 'payment';
        $response['section_html']['payment']= $this->_renderPaymentBlock();
      } 
      catch (Mage_Core_Exception $e) {
        $response['error'] = true;
        $response['message'] = ($e->getMessage() != null) ? $e->getMessage() : Mage::helper('simplecheckout')->__('We were unable to save your order');
        $gotoSection = $this->getOnepage()->getCheckout()->getGotoSection();
        if ($gotoSection) {
          $response['goto_section'] = $gotoSection;
          $this->getOnepage()->getCheckout()->setGotoSection(null);
          $updateSection = $this->getOnepage()->getCheckout()->getUpdateSection();
          if ($updateSection) {
            if (isset($this->_sectionUpdateFunctions[$updateSection])) {
              $updateSectionFunction = $this->_sectionUpdateFunctions[$updateSection];
              $updateSectionName = str_replace('-method', null, $updateSection);
              $response['section_html'][$updateSectionName] = $this->$updateSectionFunction();
            }
          }
        }
      } catch (Exception $e) {
        $response['error'] = true;
        $response['message'] = ($e->getMessage() != null) ? $e->getMessage() : Mage::helper('simplecheckout')->__('We were unable to save your order');
      }
      $this->getOnepage()->getQuote()->save();
      /**
      * when there is redirect to third party, we don't want to save order yet.
      * we will save the order in return action.
      */
      if (isset($redirectUrl)) {
          $response['redirect'] = $redirectUrl;
      }


      $this->_sendAjaxResponse($response);
    } else {
      $this->_redirect('*');
    }
  }
  
  /**
   * Set active step after returning to the previous step
   */
  public function returnAction() {
    if ($this->_checkAjaxRequest()) {
      $step = $this->getRequest()->getPost('step');
      $this->_getSimpleSession()->setActiveStep($step);
    }
    else {
      $this->_redirect('checkout/cart');
    }
  }
  
  /**
   * Show success page
   */
  public function successAction() {
    $session = $this->getOnepage()->getCheckout();
    if (!$session->getLastSuccessQuoteId()) {
      $this->_redirect('checkout/cart');
      return;
    }

    $lastQuoteId = $session->getLastQuoteId();
    $lastOrderId = $session->getLastOrderId();
    $lastRecurringProfiles = $session->getLastRecurringProfileIds();
    if (!$lastQuoteId || (!$lastOrderId && empty($lastRecurringProfiles))) {
      $this->_redirect('checkout/cart');
      return;
    }

    $session->clear();
    $this->loadLayout();
    $this->_initLayoutMessages('checkout/session');
    Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($lastOrderId)));
    $this->renderLayout();
  }

  /**
   * Save billing address in payment step
   * 
   * @return array
   * @throws Exception
   */
  protected function _saveBillingData() {

    $data = $this->_getBillingFormData();
    
    if (isset($data['same_as_shipping']) &&  $data['same_as_shipping'] == 1) {
      //copy data from shipping address
      $customerAddressId = null;
      $sameAsShipping = true;
    } else {
      $sameAsShipping = false;
      $newCustomerAddressId = $this->getRequest()->getPost('billing_address_id', false);      
      $savedCustomerAddressId = $this->getRequest()->getPost('selected_billing_address_id', false);      
      $customerAddressId = $newCustomerAddressId != 'placeholder' ? $newCustomerAddressId : $savedCustomerAddressId;
    }
    
    $result = $this->getOnepage()->saveBilling($data, $customerAddressId);
    
    if (!isset($result['error'])) {
      $this->getOnepage()->getQuote()->getBillingAddress()->setSameAsBilling($sameAsShipping)->save();
      $this->_getSimpleSession()->setBillingSameAsShipping($sameAsShipping);      
    } 
    else {
      $message = is_array($result['message']) ? join(', ', $result['message']) : $result['message'];
      throw new Exception($message);
    }

    return $result;
  }
  
  /**
   * Get Billing Address data in payment step
   * 
   * @return array
   */
  protected function _getBillingFormData() {
    $data = $this->getRequest()->getPost('billing', array());
    
    if (isset($data['same_as_shipping']) &&  $data['same_as_shipping'] == 1) {
      //get shipping data
      $data = $this->_getSimpleSession()->getShippingData();
      if (isset($data['shipping_address_id']) && $data['shipping_address_id'] != false ) {
        $shipping = $this->getOnepage()->getQuote()->getShippingAddress()->getData();
        $data = array(
          'prefix' => $shipping['prefix'],
          'firstname' => $shipping['firstname'],
          'middlename' => $shipping['middlename'],
          'lastname' => $shipping['lastname'],
          'suffix' => $shipping['suffix'],
          'company' => $shipping['company'],
          'street' => array($shipping['street']),
          'city' => $shipping['city'],
          'region' => $shipping['region'],
          'region_id' => $shipping['region_id'],
          'postcode' => $shipping['postcode'],
          'country_id' => $shipping['country_id'],
          'telephone' => $shipping['telephone'],
          'fax' => $shipping['fax']
        );
      }
      $data['same_as_shipping'] = 1;
    }
    else {
      $data['telephone'] = isset($data['telephone']) && $data['telephone'] != null ? $data['telephone'] : '1231231234';
    }
    if ($this->getOnepage()->getQuote()->getCheckoutMethod() == Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER) {
      //add customer password
      $data = array_merge($data,$this->_getSimpleSession()->getUserData());
    }
    
    $data['telephone'] = $data['telephone'] == null ? 'none' : $data['telephone'];
    $data['email'] = isset($data['email']) ? trim($data['email']) : null;
    $data['save_in_address_book'] = isset($data['save_in_address_book']) ? $data['save_in_address_book'] : 0;
    
    return $data;
  }
  
  protected function _getShippingAddress() {
    $data = $this->getRequest()->getPost('shipping', array());
    if ($data['firstname'] == null && $data['lastname'] == null && $data['city'] == null && $data['postcode'] == null && $data['address_id'] != null) {
      if (!$this->getRequest()->getPost('shipping_address_id', false)) {
        //try find saved address
        $address = $this->getOnepage()->getQuote()->getShippingAddress()->getData();
        $data = array(
            'prefix' => $address['prefix'],
            'firstname' => $address['firstname'],
            'middlename' => $address['middlename'],
            'lastname' => $address['lastname'],
            'suffix' => $address['suffix'],
            'company' => $address['company'],
            'street' => array($address['street']),
            'city' => $address['city'],
            'region' => $address['region'],
            'region_id' => $address['region_id'],
            'postcode' => $address['postcode'],
            'country_id' => $address['country_id'],
            'telephone' => $address['telephone'],
            'fax' => $address['fax']
          );
      }
    }
    
    return $data;
  }

  /**
   * Check if selected method is available for current billing address
   * 
   * @return boolean
   * @throws Exception
   */
  protected function _checkPaymentMethod() {
    $payment = $this->getRequest()->getPost('payment');

    if (!empty($payment)) {
      if(isset($payment['use_customer_balance']) && !isset($payment['method'])) {
        return true; //customer balance
      }
      $_selectedMethod = $payment['method'];
      $quote = $this->getOnepage()->getQuote();
      $store = $quote ? $quote->getStoreId() : null;
      foreach (Mage::helper('payment')->getStoreMethods($store, $quote) as $method) {
        if ($this->_canUseMethod($method, $this->getOnepage()->getQuote()) && $method->getCode() == $_selectedMethod) {
          return true;
        }
      }
    }     
    else {
      throw new Exception('No payment method selected.');
    }

    throw new Exception('Selected method is not available for the given address.');
  }

  /**
   * Check if given payment method can be used for the quote
   * 
   * @param Mage_Payment_Model_Method_Abstract $method
   * @param Mage_Sales_Model_Quote $quote
   * @return boolean
   */
  protected function _canUseMethod($method, $quote) {
    
    if (!$method->canUseForCountry($quote->getBillingAddress()->getCountry())) {
      return false;
    }
    if (!$method->canUseForCurrency($quote->getStore()->getBaseCurrencyCode())) {
      return false;
    }
    
    $total = $quote->getBaseGrandTotal();
    $minTotal = $method->getConfigData('min_order_total');
    $maxTotal = $method->getConfigData('max_order_total');
    if ((!empty($minTotal) && $total < $minTotal) || (!empty($maxTotal) && $total > $maxTotal)) {
      return false;
    }
    $total = $quote->getBaseSubtotal() + $quote->getShippingAddress()->getBaseShippingAmount();
    if ($total < 0.0001 && $method->getCode() != 'free'
        && !($method->canManageRecurringProfiles() && $quote->hasRecurringItems())
        ) {
      return false;
    }
    return true;
  }
  
  /**
   * Render single step block
   * 
   * @param string $step
   * @return string
   */
  protected function _renderStepBlock($step) {
    $layout = $this->getLayout();
    /**
     * reset layout cache id
     */
    $update = $layout->getUpdate()
                    ->resetUpdates()
                    ->resetHandles()
                    ->setCacheId('LAYOUT_'.Mage::app()->getStore()->getId().md5('simplecheckout_step_' . $step));
    $update->load('simplecheckout_step_' . $step);
    $layout->generateXml();
    $layout->generateBlocks();
    $output = $layout->getOutput();
    return $output;
  }
  
  /**
   * Render payment block
   * 
   * @return string
   */
  protected function _renderPaymentBlock() {
    return $this->_renderStepBlock('payment');
  }
  
  /**
   * Render review block
   * 
   * @return string
   */
  protected function _renderReviewBlock() {
    return $this->_renderStepBlock('review');
  }

  /**
   * Prepare billing data before saving
   * 
   * @param array $data
   * @return array
   */
  protected function _prepareBillingData($data) {
    if (Mage::helper('simplecheckout')->isCustomerLoggedIn()) {
      $customer = Mage::helper('simplecheckout')->getCustomer();
      $data['email'] = $customer->getEmail();
    } else {
      //for not registered users
      $userData = $this->_getSimpleSession()->getUserDat();
      $data['email'] = $userData['email'];
    }
    return $data;
  }

  /**
   * Check if checkout process has already started
   * 
   * @return boolean
   */
  protected function _isCheckoutStarted() {
    return count($this->_getSimpleSession()->getStepData());
  }
  
  protected function _getCustomer()
  {
    $customer = Mage::registry('current_customer');
    if (!$customer) {
        $customer = $this->_getModel('customer/customer')->setId(null);
    }
    if ($this->getRequest()->getParam('is_subscribed', false)) {
        $customer->setIsSubscribed(1);
    }
    /**
     * Initialize customer group id
     */
    $customer->getGroupId();

    return $customer;
  }
  
  /**
   * Validate new customer email is unique
   * 
   * @return array|boolean
   */
  protected function _validateNewCustomer() {
    $customer = Mage::getModel('customer/customer');
    $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
    $customer->loadByEmail($this->getRequest()->getPost('email'));
    if ($customer->getId()) {
        return array(
            'error' => 1, 
            'message' => array(Mage::helper('checkout')->__('There is already a customer registered using this email address. Please login using this email address or enter a different email address to register your account.')));
    }
    return false;
  }
  
  /**
   * Get the url for the given step and current checkout type
   * 
   * @param string $step
   * @return string
   */
  protected function _getNavigatorState($step) {
    return Mage::getUrl('checkout/' . Mage::helper('simplecheckout')->getCheckoutType() . '/' . $step, array('_secure' => Mage::helper('simplecheckout')->isConnectionSecure()));
  }
  
  /**
   * Mark given step as completed
   * 
   * @param type $step
   */
  protected function _setStepComplete($step) {
    $this->_getSimpleSession()->setStepComplete($step);
  }
  
  /**
   * Send response in json format
   * 
   * @param array $response
   */
  protected function _sendAjaxResponse($response) {
    $this->getResponse()
          ->clearHeaders()
          ->setHeader('Content-Type', 'application/json')
          ->setBody(Mage::helper('core')->jsonEncode($response));
  }
  
  /*
   * Actions from F and R Controllers
   */
  public function shippingAction() {
    $this->_dispatchStep('shipping');
  }
  
  public function paymentAction() {
    $this->_dispatchStep('payment');
  }
  
  public function reviewAction() {
    $this->_redirect('*/*/payment');
  }
  
  protected function _dispatchStep($step) {
    if ($this->_getSimpleSession()->canShowStep($step)) {
      $this->_getSimpleSession()->setActiveStep($step);
      $this->_forward('index');
    }
    else {
      $this->_redirect('checkout/' . Mage::helper('simplecheckout')->getCheckoutType() .'/' . $this->_getSimpleSession()->getCurrentStep);
    }
  }
}