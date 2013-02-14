<?php

class Clearandfizzy_Reducedcheckout_Model_Observer extends Mage_Core_Model_Observer {

	public function checkReducedCheckout(Varien_Event_Observer $observer) {

		$enabled = Mage::getStoreConfig('clearandfizzy_reducedcheckout_settings/reducedcheckout/isenabled');

		// return early if we're not enabled
		if ($enabled != true) {
			return;
		} // end

		$handles = $observer->getEvent()->getLayout()->getUpdate()->getHandles();

		// find the handle we're looking for
		if ( array_search('checkout_onepage_index', $handles) == true ) {

			// add our own
			$update = $observer->getEvent()->getLayout()->getUpdate();
			$update->addHandle('clearandfizzy_checkout_reduced');

			// should we remove the login step..
			if (Mage::helper('clearandfizzy_reducedcheckout/data')->isGuestCheckoutOnly() == true) {
				$update->addHandle('clearandfizzy_checkout_reduced_forceguestonly');
			} // end


		} // end


		return;

	} // end


} // end

