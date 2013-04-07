<?php

class Orba_Payupl_Block_Form extends Mage_Payment_Block_Form
{
    /**
     * Payment method code
     * @var string
     */
    protected $_methodCode = 'payupl';

    
    /**
     * Set template and redirect message
     */
    protected function _construct()
    {
        $this->setTemplate('payupl/form.phtml');
        $this->setMethodTitle('');
        $this->setMethodLabelAfterHtml('<img src="'.Mage::getDesign()->getSkinUrl('images/payupl/logo.jpg').'" height="20" alt="Payu.pl"/> '.Mage::helper('payupl')->__('Credit Card or E-transfer'));
        
        return parent::_construct();
    }

    /**
     * Payment method code getter
     * @return string
     */
    public function getMethodCode()
    {
        return $this->_methodCode;
    }
}