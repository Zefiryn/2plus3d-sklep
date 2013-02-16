<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @copyright   Copyright (c) 2010 Artio (http://www.artio.net)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Artio_MTurbo_Block_Adminhtml_Edit_Tab_License extends Artio_MTurbo_Block_Adminhtml_Edit_Tab_Abstract
{
	
	/**
	 * @var Varien_Data_Form
	 */
	private $form;
	private $regInfo;

    public function __construct() {
        parent::__construct();
        $this->setId('license_section');
        $this->_title = $this->getMyHelper()->__('Registration');
        $this->regInfo = Mage::helper('mturbo/info')->getRegInfo();
    }

    protected function _prepareForm() {

        $config = Mage::getSingleton('mturbo/config');
        if (!empty($this->regInfo)) {
        	$data = array_merge($config->getData(), get_object_vars($this->regInfo));	
        }

        $this->form = new Varien_Data_Form();
        $this->_addLicenseFieldset(); 
        
        $this->form->setValues($data);
        $this->setForm($this->form);

        return parent::_prepareForm();
        
    }
    
    private function _addLicenseFieldset() {
    	
    	$layoutFieldset = $this->form->addFieldset('license_fieldset', array(
            'legend' => $this->getMyHelper()->__('Registration'),
            'class'  => 'fieldset'
        ));
        $layoutFieldset->addType('noesclabel', Artio_MTurbo_Helper_Data::FORM_NO_ESC_LABEL);
        
        $layoutFieldset->addField('download_id', 'text', array(
            'name'      => 'download_id',
            'label'     => $this->getMyHelper()->__('Your Download ID').':',
        	'value'		=> ''
        ));
        
        
        if ($this->regInfo != null) {
        
        	if (!empty($this->regInfo->name))
        		$layoutFieldset->addField('name', 'label', array(
            		'name'      => 'name',
            		'label'     => $this->getMyHelper()->__('Name').':',
            		'style'     => 'height:24em;',
            		'disabled'  => true,	
        		));
        
        	if (!empty($this->regInfo->company))
        		$layoutFieldset->addField('company', 'label', array(
            		'name'      => 'company',
            		'label'     => $this->getMyHelper()->__('Company').':',
            		'style'     => 'height:24em;',
            		'disabled'  => true,
        		));
        
        	if (!empty($this->regInfo->date))
        		$layoutFieldset->addField('date', 'label', array(
            		'name'      => 'date',
            		'label'     => $this->getMyHelper()->__('Date').':',
            		'style'     => 'height:24em;',
            		'disabled'  => true,
        			'value'		=> 'x'
        		));
        
        	if (!empty($this->regInfo->message)) { 
        		$layoutFieldset->addField('message', 'noesclabel', array(
            		'name'      => 'message',
            		'label'     => $this->getMyHelper()->__('Status').':',
            		'bold'      => true,
            		'disabled'  => true
        		));
        	}
        
        } else {

       	 	$layoutFieldset->addField('message', 'noesclabel', array(
            	'name'      => 'message',
            	'label'     => $this->getMyHelper()->__('Status').':',
            	'style'     => 'height:24em;',
       	 		'bold'		=> true,
            	'disabled'  => true,
        	));	
        	
        }

    }

    
}
