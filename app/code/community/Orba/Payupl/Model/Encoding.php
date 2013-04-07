<?php

class Orba_Payupl_Model_Encoding extends Mage_Core_Model_Abstract {
    
    const PAYUPL_ENCODING_UTF = 'UTF';
    
    public function toOptionArray() {
        return array(
            array(
                'value' => Orba_Payupl_Model_Encoding::PAYUPL_ENCODING_UTF,
                'label' => 'UTF-8'
            )
        );
    }
    
}