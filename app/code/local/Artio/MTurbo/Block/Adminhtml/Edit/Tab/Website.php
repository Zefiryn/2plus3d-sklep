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
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @copyright   Copyright (c) 2010 Artio (http://www.artio.net)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Configuration tab for websites. 
 * Number of fieldsets equals to number of enabled website.
 * Demo version has only one website.
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
class Artio_MTurbo_Block_Adminhtml_Edit_Tab_Website extends Artio_MTurbo_Block_Adminhtml_Edit_Tab_Abstract
{
	
	/**
	 * @var Varien_Data_Form
	 */
	private $form;

	/**
	 * Constructor.
	 */
    public function __construct() {
        parent::__construct();
        $this->setId('website_section');
        $this->_title = $this->getMyHelper()->__('Websites Configuration');
    }

    /**
     * (non-PHPdoc)
     * @see Mage_Adminhtml_Block_Widget_Form::_prepareForm()
     */
    protected function _prepareForm() {

    	$config = Mage::getSingleton('mturbo/config');

    	/* make form */
        $this->form = new Varien_Data_Form();
        
        /* for every website add one fieldset */
        $websiteCollection = Mage::getModel('core/website')->getCollection()->load();
		foreach ($websiteCollection as $website) {
		    if ($website->getDefaultStore()) {
			    $this->_addWebsiteFieldset($website);
			    break;
		    }
		}
        
        /* bind data */
        $this->form->setValues(Mage::getSingleton('mturbo/config_websiteTransformer')->configToForm($config));
        $this->setForm($this->form);

        return parent::_prepareForm();
        
    }
    
       
    /**
     * Method add website fieldset to the form.
     * @param Mage_Core_Model_Website $website
     */
    private function _addWebsiteFieldset($website) {
    	
    	$prefixWeb = 'website-'.$website->getCode();
    	
    	/* make fieldset */
    	$layoutFieldset = $this->form->addFieldset($prefixWeb.'_fieldset', array(
            'legend' => $this->getMyHelper()->__($website->getName() . ' settings'),
            'class'  => 'fieldset'));
    	
    	/* add extra user control */
    	$layoutFieldset->addType('html_element', Artio_MTurbo_Helper_Data::FORM_HTML);
    	$layoutFieldset->addType('widget_button', Artio_MTurbo_Helper_Data::FORM_WIDGET_BUTTON);
    	
    	/* indicator whether website is enabled */
        $layoutFieldset->addField($prefixWeb.'-enabled', 'select', array(
            'name'      => $prefixWeb.'-enabled',
            'label'     => $this->getMyHelper()->__('Enable website').':',
        	'options'	=> array(
        					0 => $this->getMyHelper()->__('No'),
        					1 => $this->getMyHelper()->__ ( 'Yes' ))));
    	
    	/* add field for base dir */
        $layoutFieldset->addField($prefixWeb.'-base_dir', 'text', array(
            'name'      => $prefixWeb.'-base_dir',
            'label'     => $this->getMyHelper()->__('Base directory').':'));
        
        /* add field for server name */
        $layoutFieldset->addField($prefixWeb.'-server_name', 'text', array(
            'name'      => $prefixWeb.'-server_name',
            'label'     => $this->getMyHelper()->__('Server name').':'));

        $layoutFieldset->addField ( $prefixWeb.'_dec1', 'html_element', 
        array ('label' => '<h4>'.$this->getMyHelper()->__('Enable/Disable Storeview').'</h4>',
				   'code' => '<div style="height:10px;border-bottom:1px solid #808080"></div>'));
        
        /* every store has one select determines whether enabled is */
        foreach ($website->getStores() as $store)
        	if ($store->getIsActive())
        		$layoutFieldset->addField($prefixWeb.'-store-'.$store->getCode(), 'select', array(
        			'name' 		=> $prefixWeb.'-store-'.$store->getCode(),
        			'label'		=> $store->getGroup()->getName().'<br />'.$store->getName(),
        			'options'	=> array(
        							0 => $this->getMyHelper()->__('No'),
        							1 => $this->getMyHelper()->__ ( 'Yes' ))));
        							
        $layoutFieldset->addField ( $prefixWeb.'_dec2', 'html_element', 
			array ('label' => '<h4>'.$this->getMyHelper()->__('Htaccess settings').'</h4>',
				   'code'  => '<div style="height:10px;border-bottom:1px solid #808080"></div>' ));
			
		
		/* get htaccess state and set color by it */
		$htaccess = Mage::getModel('mturbo/htaccess')->setWebsiteCode($website->getCode());
		$state = '';
		$pathToHtaccess = $htaccess->getPathToBaseHtaccess();
		$color = (Mage::helper('mturbo/functions')->get_file_state($pathToHtaccess, $state, 'ew')) ? 'green' : 'red';
		
		$layoutFieldset->addField ( $prefixWeb.'_dec3', 'html_element', 
			array ('label' => $this->getMyHelper()->__('File .htaccess path'),
				   'code'  => '<span>'.$pathToHtaccess.'</span>' ));
			
		$edit = $htaccess->isEditedByMTurbo() ? 'yes' : 'no';
			
		$layoutFieldset->addField ( $prefixWeb.'_dec5', 'html_element', 
			array ('label' => $this->getMyHelper()->__('Edited by MTurbo'),
				   'code'  => '<span><b>'.$this->getMyHelper()->__($edit).'</b></span>' ));
		
		$layoutFieldset->addField ( $prefixWeb.'_dec4', 'html_element', 
			array ('label' => $this->getMyHelper()->__('File .htaccess state'),
				   'code'  => '<span style="color:'.$color.'">'.$this->getMyHelper()->__($state).'</span>' ));
			
		/* button 'rebuild' show only if htaccess is ready */
		if ($color=='green')
		$layoutFieldset->addField($prefixWeb.'_htaccess_button', 'widget_button', array(
        	'name'		=> $prefixWeb.'_htaccess_button',
        	'label'		=> $this->getMyHelper()->__('Rebuild .htaccess for this website'),
        	'onclick'	=> "setLocation('" . Mage::helper('adminhtml')->getUrl('*/*/htaccessbuild', array('websitecode'=>$website->getCode())) . "')"
        ));
        
		$layoutFieldset->addField($prefixWeb.'_urllist_button', 'widget_button', array(
        	'name'		=> $prefixWeb.'_urllist_button',
        	'label'		=> $this->getMyHelper()->__('Generate URL list for this website'),
        	'onclick'	=> "setLocation('" . Mage::helper('adminhtml')->getUrl('*/*/generateurllist', array('websitecode'=>$website->getCode())) . "')"
        ));
    	
    }   
 
}
