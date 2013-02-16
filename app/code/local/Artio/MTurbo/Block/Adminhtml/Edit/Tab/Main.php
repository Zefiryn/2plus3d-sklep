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
 * Configuration tab contains turbopath settings, download method chooser, 
 * automatic downloader settings (only full version), filesize viewing settings and
 * backup of htaccess settings.
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
class Artio_MTurbo_Block_Adminhtml_Edit_Tab_Main extends Artio_MTurbo_Block_Adminhtml_Edit_Tab_Abstract
{
	
	/**
	 * Form on this tab.
	 * @var Varien_Data_Form
	 */
	private $form;
	
	/**
	 * Constructor. Id of this section is 'main_section'.
	 */
    public function __construct() { 	
        parent::__construct();
        
        $this->setId('main_section');
        $this->_title = $this->getMyHelper()->__('Main Configuration');
    }

    protected function _prepareForm() {

    	$config = Mage::getSingleton('mturbo/config');

    	/* make form */
        $this->form = new Varien_Data_Form();
        $this->_addTurboPathFieldset();
        $this->_addDownloadMethodFieldset();
        $this->_addViewSizeMethodFieldset();
        $this->_addHtaccessFieldset();

        /* bind data */
        $this->form->setValues($config->getData());
        $this->setForm($this->form);

        return parent::_prepareForm();
        
    }
    
    /**
     * Fieldset for turbo path.
     */
    private function _addTurboPathFieldset() {
    	 	
    	/* make fieldset */
    	$layoutFieldset = $this->form->addFieldset('turbopath_fieldset', array(
            'legend' => $this->getMyHelper()->__('Turbopath directory'),
            'class'  => 'fieldset'));
        
        /* add field for turbopath */
        $layoutFieldset->addField('turbopath', 'text', array(
            'name'      => 'turbopath',
            'label'     => $this->getMyHelper()->__('Relative path from webroot').':',
        	'value'		=> 'var'.DS.'turbocache'));
        	
    }
    
    /**
     * Fieldset for automatic downloader settings.
     */
    private function _addAutomaticDownloadFieldset() {
    	
    	/* make fieldset */
    	$layoutFieldset = $this->form->addFieldset('download_fieldset', array(
            'legend' => $this->getMyHelper()->__('Automatic cache management'),
            'class'  => 'fieldset'));
    	
    	$layoutFieldset->addType('html_element', Artio_MTurbo_Helper_Data::FORM_HTML);
        
        /* indicator whether automatic download is enabled */
        $layoutFieldset->addField('automatic_download', 'select', array(
            'name'      => 'automatic_download',
            'label'     => $this->getMyHelper()->__('Enable automatic cache refresh').':',
        	'options'	=> array(
        					0 => $this->getMyHelper()->__('No'),
        					1 => $this->getMyHelper()->__ ( 'Yes' ))));
		
        /* automatic download time */
		$layoutFieldset->addType ( 'crontime', Artio_MTurbo_Helper_Data::FORM_CRON_HOUR_TIME );
		$layoutFieldset->addField ( 'automatic_download_time', 'crontime', array ('name' => 'automatic_download_time', 'label' => $this->getMyHelper ()->__ ( 'Download time' ) . ':', 'style' => 'display:inline;width:40px;' ) );
		
		/* label with last download */
		$layoutFieldset->addField ( 'lastdownload', 'label', array ('name' => 'lastdownload', 'label' => $this->getMyHelper ()->__ ( 'Last download' ) . ':', 'style' => 'height:24em;', 'disabled' => true ) );
	
		$scriptPath = Mage::helper('mturbo')->getFullDownloadScriptPath();
		if (!is_executable($scriptPath)) {
			$layoutFieldset->addField ( 'scriptstate', 'html_element', 
			array ('label' => '<h4>'.$this->getMyHelper()->__('Script state').'</h4>',
				   'code' => '<span style="color:red">'.Mage::helper('mturbo')->__("Script '%s' is not executable. Please change permission", $scriptPath).'</span>'));
		}
		
	}
	
	/** 
	 * Fieldset for choose download method. 
	 */
	private function _addDownloadMethodFieldset() {
				
		/* make fieldset */
		$layoutFieldset = $this->form->addFieldset ( 'downloadmethod_fieldset', array ('legend' => $this->getMyHelper ()->__ ( 'Choice download method' ), 'class' => 'fieldset' ) );

		/* select box for choose download method */
		$layoutFieldset->addType ('selectdmet', Artio_MTurbo_Helper_Data::FORM_SELECT_DOWN );
  	  	$layoutFieldset->addField('download_method', 'selectdmet', array(
            'name'      => 'download_method',
            'label'     => $this->getMyHelper()->__('Download method').':',
        	'options'	=> Mage::getModel('mturbo/downloadMethodsFactory')->getList()));
    	
        /* button for patch Mage.php */
        $label = Mage::getModel('mturbo/patch')->isPatched() ?
        	Mage::helper('mturbo')->__('Remove Mage Patch') :
        	Mage::helper('mturbo')->__('Apply Mage Patch');
        
        $layoutFieldset->addType('widget_button', Artio_MTurbo_Helper_Data::FORM_WIDGET_BUTTON);
        $layoutFieldset->addField('patch_button', 'widget_button', array(
        	'name'		=> 'patch_button',
        	'label'		=> $label,
        	'onclick'	=> "setLocation('" . Mage::helper('adminhtml')->getUrl('*/*/magepatch') . "')",
        	'comment'	=> $this->getMyHelper()->__('Apply the patch only just in case you need to use Direct Access download method!')));
  
    }
    
    /**
     * Add fieldset for settings views on filesize in the grid.
     */
    private function _addViewSizeMethodFieldset() {
    	
    	/* make fieldset */
		$layoutFieldset = $this->form->addFieldset ( 'viewsize_fieldset', array ('legend' => $this->getMyHelper ()->__ ( 'Filesize viewing' ), 'class' => 'fieldset' ) );
    	
		/* add field for minimal page size */
        $layoutFieldset->addField('minimal_page_size', 'text', array(
            'name'      => 'minimal_page_size',
            'label'     => $this->getMyHelper()->__('The minimum size to decision, the page is alright (bytes)').':',
        	'value'		=> '512'));
    	
    }
    
    /**
     * Add fieldset for htaccess settings. 
     */
    private function _addHtaccessFieldset() {
    	
    	/* make fieldset */
		$layoutFieldset = $this->form->addFieldset ( 'htaccess_fieldset', array ('legend' => $this->getMyHelper ()->__ ( 'Htaccess backup settings' ), 'class' => 'fieldset' ) );
    	
		$layoutFieldset->addField('enabled_htaccess_backup', 'select', array(
           	'name'      => 'enabled_htaccess_backup',
           	'label'     => $this->getMyHelper()->__('Enable making htaccess backup').':',
        	'options'	=> array(
        					0 => $this->getMyHelper()->__('No'),
        					1 => $this->getMyHelper()->__ ( 'Yes' ))));
		
        $layoutFieldset->addField('number_of_htaccess_backups', 'text', array(
            'name'      => 'number_of_htaccess_backups',
            'label'     => $this->getMyHelper()->__('The maximum number of htaccess backups').':',
        	'value'		=> '10'));
    	
    }
    
}
