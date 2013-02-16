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
class Artio_MTurbo_Block_Adminhtml_Edit_Tab_Uninstall extends Artio_MTurbo_Block_Adminhtml_Edit_Tab_Abstract
{
	
	/**
	 * @var Varien_Data_Form
	 */
	private $form;

    public function __construct() {
        parent::__construct();
        $this->setId('action_section');
        $this->_title = $this->getMyHelper()->__('Uninstall');
    }

    protected function _prepareForm() {

    	$config = Mage::getSingleton('mturbo/config');

        $this->form = new Varien_Data_Form();
        $this->_addActionFieldset();
        
        $this->form->setValues($config->getData());
        $this->setForm($this->form);

        return parent::_prepareForm();
        
    }

    private function _addActionFieldset() {
    	
    	$layoutFieldset = $this->form->addFieldset('uninstall_fieldset', array(
            'legend' => $this->getMyHelper()->__('Uninstall M-Turbo'),
            'class'  => 'fieldset'
        ));
        
        $layoutFieldset->addType('widget_button', Artio_MTurbo_Helper_Data::FORM_WIDGET_BUTTON);
        $layoutFieldset->addField('clear_button', 'widget_button', array(
        	'name'		=> 'clear_button',
        	'label'		=> $this->getMyHelper()->__('Uninstall M-Turbo'),
        	'style'		=> 'width:850px',
        	'onclick'	=> "setLocation('" . Mage::helper('adminhtml')->getUrl('*/*/uninstall') . "')",
        	'comment'	=> $this->getMyHelper()->__('This action completely removes M-Turbo from your Magento')
        ));

    	
    }
    
}
