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
class Artio_MTurbo_Block_Adminhtml_Edit_Tab_Actions extends Artio_MTurbo_Block_Adminhtml_Edit_Tab_Abstract
{
	
	/**
	 * @var Varien_Data_Form
	 */
	private $form;

    public function __construct() {
        parent::__construct();
        $this->setId('action_section');
        $this->_title = $this->getMyHelper()->__('Actions');
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
    	
    	$layoutFieldset = $this->form->addFieldset('action_fieldset', array(
            'legend' => $this->getMyHelper()->__('Cache actions'),
            'class'  => 'fieldset'
        ));
        
        $layoutFieldset->addType('widget_button', Artio_MTurbo_Helper_Data::FORM_WIDGET_BUTTON);
        $layoutFieldset->addField('clear_button', 'widget_button', array(
        	'name'		=> 'clear_button',
        	'label'		=> $this->getMyHelper()->__('Remove all cached pages'),
        	'onclick'	=> "setLocation('" . Mage::helper('adminhtml')->getUrl('*/*/clearpages') . "')",
        	'comment'	=> $this->getMyHelper()->__('Purge all cached pages and relevant directories located on the disk. No caching will be done until cache is recreated.')
        ));
        
        $layoutFieldset->addField('syn_button', 'widget_button', array(
        	'name'		=> 'syn_button',
        	'label'		=> $this->getMyHelper()->__('Update URLs from Rewrite Table'),
        	'onclick'	=> "setLocation('" . Mage::helper('adminhtml')->getUrl('*/*/synchronize') . "')",
        	'comment'	=> $this->getMyHelper()->__('Launching this action will update cached URLs based on current values from the Rewrite Table. We recommend runing this action after major modifications to the products or categories.')
        ));
        
        $layoutFieldset->addField('generate_button', 'widget_button', array(
        	'name'		=> 'generate_button',
        	'label'		=> $this->getMyHelper()->__('Generate URL list for all websites'),
        	'onclick'	=> "setLocation('" . Mage::helper('adminhtml')->getUrl('*/*/generateurllist') . "')",
        	'comment'	=> $this->getMyHelper()->__(' Generate a list of the URLs and store it on the disk. This action will be executed if Automatic cache management is enabled.')
        ));
        
        $layoutFieldset->addField('download_button', 'widget_button', array(
        	'name'		=> 'download_button',
        	'label'		=> $this->getMyHelper()->__('Cache all pages'),
        	'onclick'	=> "window.open('" . Mage::helper('adminhtml')->getUrl('*/*/download') . "')",
        	'comment'	=> $this->getMyHelper()->__('Create cache for all pages (except blocked pages). Download progress can be monitored in a separate window. Please, do not close this window. Update of URLs from the Rewrite Table will be started  automatically before caching is executed. Please, note that initial cache creation may take a long time (several hours). Time needed depends on the number of pages your site has.')
        	
        ));
        
        $layoutFieldset->addField('htaccess_button', 'widget_button', array(
        	'name'		=> 'htaccess_button',
        	'label'		=> $this->getMyHelper()->__('Rebuild .htaccess for all websites'),
        	'onclick'	=> "setLocation('" . Mage::helper('adminhtml')->getUrl('*/*/htaccessbuild') . "')",
        	'comment'	=> $this->getMyHelper()->__('Rebuild .htaccess for all websites.'),
        	
        ));
    	
    }
    
}
