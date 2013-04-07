<?php

class Orba_Payupl_Model_Gateway extends Mage_Core_Model_Abstract {
    
    const PAYUPL_GATEWAY_PRODUCTION = 'https://www.platnosci.pl/paygw';
    const PAYUPL_GATEWAY_SANDBOX = 'https://sandbox.payu.pl/paygw';
    
    public function toOptionArray() {
        return array(
            array(
                'value' => Orba_Payupl_Model_Gateway::PAYUPL_GATEWAY_PRODUCTION,
                'label' => Mage::helper('payupl')->__('Production')
            ),
            array(
                'value' => Orba_Payupl_Model_Gateway::PAYUPL_GATEWAY_SANDBOX,
                'label' => Mage::helper('payupl')->__('Sandbox')
            )
        );
    }
    
}