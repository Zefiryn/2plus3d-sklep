<?php

class Clearandfizzy_Reducedcheckout_Model_System_Config_Source_Shipping_EnabledMethods {

	public function toOptionArray()
	{

		$active_carriers = Mage::getSingleton('shipping/config')->getActiveCarriers();

		$carrier_methods = array();

		foreach ( $active_carriers as $code => $carrier ) {
			$label = Mage::getStoreConfig('carriers/'.$code.'/title');
			$enabled = Mage::getStoreConfig('carriers/'.$code. '/active');

			$methods = $carrier->getAllowedMethods();

			foreach ($methods as $method_code => $method_label ) {

				if ( $label != null && $enabled == 1 ) {
					$carrier_methods[$code.'_'.$method_code] = $label . " [".$method_label."]";
				} // end
			}

		} // end

		return $carrier_methods;

	} // end fun


} // end class
