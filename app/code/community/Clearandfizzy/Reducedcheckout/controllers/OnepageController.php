<?php
require_once "Mage/Checkout/controllers/OnepageController.php";
class Clearandfizzy_Reducedcheckout_OnepageController extends Mage_Checkout_OnepageController
{
	public $layout;

	/**
	 * Save checkout method - set to guest method
	 */
	public function saveMethodAction()
	{
		if ($this->_expireAjax()) {
			return;
		} // end if


		if ($this->getRequest()->isPost()) {
			$method = $this->getCheckoutMethod();
			$result = $this->getOnepage()->saveCheckoutMethod($method);
		} // end if

	} // end if


	private function getCheckoutMethod() {

		switch ( Mage::helper('clearandfizzy_reducedcheckout/data')->isGuestCheckoutOnly() ) {

			case true:
				$method = "guest";
			break;

			default:
				$method = $this->getRequest()->getPost('method');
			break;

		} // end

		return $method;

	} /// end


	/**
	 * (non-PHPdoc)
	 * @see Mage_Checkout_OnepageController::saveShippingMethodAction()
	 */
	public function saveShippingMethodAction() {

		if ($this->_expireAjax()) {
			return;
		} // end if

		// run the shipping methods Layout, this is required to setup the checkout process correctly
		//$this->_getShippingMethodsHtml();

		$shipping = Mage::helper('clearandfizzy_reducedcheckout')->getShippingMethod();
		$result = $this->getOnepage()->saveShippingMethod($shipping);

		$this->getOnepage()->getQuote()->collectTotals();
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		$this->getOnepage()->getQuote()->collectTotals()->save();

		// save shipping method event
		Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method',
				array('request'=>$this->getRequest(),
						'quote'=>$this->getOnepage()->getQuote()));


		$this->getOnepage()->getQuote()->setTotalsCollectedFlag(false);

	} // end

	/**
	 * (non-PHPdoc)
	 * @see Mage_Checkout_OnepageController::savePaymentAction()
	 */
	public function savePaymentAction() {

		if ($this->_expireAjax()) {
			return;
		} // end if

		//$this->_getPaymentMethodsHtml();

		// set payment to quote
		$payment = Mage::helper('clearandfizzy_reducedcheckout')->getPaymentMethod();
		$data = array('method' => $payment);

		$result = $this->getOnepage()->savePayment($data);


		// get section and redirect data
		$redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();

	} // end


	public function saveShippingAction() {
		if ($this->_expireAjax()) {
			return;
		}

		if ($this->getRequest()->isPost()) {

			$data = $this->getRequest()->getPost('shipping', array());
			$customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);

			// save the billing address info
			$result = $this->getOnepage()->saveShipping($data, $customerAddressId);

			// set the checkout method
			$this->saveMethodAction();

			// set the shipping method
			$this->saveShippingMethodAction();

			// set the payment method
			$this->savePaymentAction();

			// render the onepage review
			if (!isset($result['error'])) {

				$result['allow_sections'] = array('review');
				$result['goto_section'] = 'review';
				$result['update_section'] = array(
						'name' => 'review',
						'html' => $this->_getReviewHtml()
				);

			} // end

			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		}


	} // end

	/**
	 * save checkout billing address
	 */
	public function saveBillingAction()
	{
		if ($this->_expireAjax()) {
			return;
		}

		if ($this->getRequest()->isPost()) {

			if ( Mage::helper('clearandfizzy_reducedcheckout/data')->isGuestCheckoutOnly() == true) {
				// set the checkout method
				$this->saveMethodAction();
			} // end if

			$data = $this->getRequest()->getPost('billing', array());
			$customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

			if (isset($data['email'])) {
				$data['email'] = trim($data['email']);
			} // end if

			// save the billing address info
			$result = $this->getOnepage()->saveBilling($data, $customerAddressId);

			// set the shipping method
			$this->saveShippingMethodAction();

			// set the payment method
			$this->savePaymentAction();


			// render the onepage review
			if (!isset($result['error'])) {

				/* check quote for virtual */
				if ($this->getOnepage()->getQuote()->isVirtual()) {

	                $result['goto_section'] = 'review';
	                $result['update_section'] = array(
	                    'name' => 'review',
	                    'html' => $this->_getReviewHtml()
	                );

				} elseif (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {
	                $result['goto_section'] = 'review';
	                $result['update_section'] = array(
	                    'name' => 'review',
	                    'html' => $this->_getReviewHtml()
	                );

					$result['allow_sections'] = array('review');
					$result['duplicateBillingInfo'] = 'true';
				} else {
					$result['goto_section'] = 'shipping';
				} // end if
			} // end

			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		}
	} // end


	/**
	 * (non-PHPdoc)
	 * @see Mage_Checkout_OnepageController::_getReviewHtml()
	 */
	protected function _getReviewHtml() {

/*
		$this->getOnepage()->getQuote()->getShippingAddress()->collectShippingRates()->save();
		$this->getOnepage()->getQuote()->getShippingAddress()->setShippingMethod('flatrate_flatrate');
		$this->getOnepage()->getQuote()->collectTotals();
		$this->getOnepage()->getQuote()->collectTotals()->save();
		$this->getOnepage()->getQuote()->setTotalsCollectedFlag(false);
*/
	 	$layout = $this->getLayout();
		$update = $layout->getUpdate();
		$update->merge('checkout_onepage_review');

		$layout->generateXml();
		$layout->generateBlocks();

		$output = $layout->getBlock('root')->toHtml();
		return $output;

	} // end

	public function progressAction()
	{
		$layout = $this->getLayout();
		$update = $layout->getUpdate();
		$update->load('checkout_onepage_progress');
		$layout->generateXml();
		$layout->generateBlocks();
		$output = $layout->getOutput();
		$this->renderLayout();
	}

} // end class

