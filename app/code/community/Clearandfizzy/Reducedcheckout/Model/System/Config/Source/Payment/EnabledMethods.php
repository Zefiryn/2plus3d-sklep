<?php

class Clearandfizzy_Reducedcheckout_Model_System_Config_Source_Payment_EnabledMethods {

	public function toOptionArray()
	{

		$active_methods = Mage::getSingleton('payment/config')->getActiveMethods();

		$methods = array();
		foreach ( $active_methods as $code => $value ) {
			$label = Mage::getStoreConfig('payment/'.$code.'/title');
			$methods[$code] = $label;
		} // end

		return $methods;

	} // end fun


} // end class