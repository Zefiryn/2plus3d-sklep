<?php

class Clearandfizzy_Reducedcheckout_Helper_Data extends Mage_Core_Helper_Abstract {

	public function getPaymentMethod() {
		$value = Mage::getStoreConfig('clearandfizzy_reducedcheckout_settings/reducedcheckout/default_payment');
		return $value;
	} // end

	public function getShippingMethod() {
		$value = Mage::getStoreConfig('clearandfizzy_reducedcheckout_settings/reducedcheckout/default_shipping');
		return $value;
	} // end

	public function isGuestCheckoutOnly() {
		$value = Mage::getStoreConfig('clearandfizzy_reducedcheckout_settings/reducedcheckout/guestcheckoutonly');
		return $value;
	} // end

}

