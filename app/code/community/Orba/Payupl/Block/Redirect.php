<?php

class Orba_Payupl_Block_Redirect extends Mage_Core_Block_Template {
    
    public function getForm() {
        $payment = Mage::getModel('payupl/payment');

        $form = new Varien_Data_Form();
        $form->setAction($payment->getConfig()->getGatewayUrl() . '/' . $payment->getConfig()->getEncoding() . '/NewPayment')
                ->setId('payupl_checkout')
                ->setName('payupl_checkout')
                ->setMethod('POST')
                ->setUseContainer(true);

        foreach ($payment->getRedirectData($this->getOrder()) as $field => $value) {
            $form->addField($field, 'hidden', array('name' => $field, 'value' => $value));
        }
        return $form->toHtml();
    }

}
