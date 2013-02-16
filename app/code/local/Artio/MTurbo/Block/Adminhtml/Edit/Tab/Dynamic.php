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
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * 
 *
 * @category   Artio
 * @package    Artio_MTurbo
 * @author     Artio Magento Team <jiri.chmiel@artio.cz>
 */

class Artio_MTurbo_Block_Adminhtml_Edit_Tab_Dynamic
    extends Artio_MTurbo_Block_Adminhtml_Edit_Tab_Abstract
{
	
    /**
     * Constructor.
     */
	public function __construct() {
		parent::__construct();
		$this->setId('dynamic_section');
		$this->_title = $this->getMyHelper()->__('Dynamic loaded blocks');
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Mage_Adminhtml_Block_Widget_Form::_prepareForm()
	 */
    protected function _prepareForm() {
    	
        /* @var $config Artio_MTurbo_Model_Config */
    	$config = Mage::getSingleton('mturbo/config');			
    	
    	$form = new Varien_Data_Form();

    	/* @var $layoutPatch Artio_MTurbo_Model_LayoutPatch */
    	$layoutPatch = Mage::getSingleton('mturbo/layoutPatch');
    	if ($layoutPatch->needToPatch()) {
        	$this->_addLayoutPatchFieldset($form);
    	}
    	 
    	if (!$layoutPatch->needToPatch() || $layoutPatch->isPatched()) {
    	
        	/* fieldset for automatic refresh */
        	$layoutFieldsetDefault = $form->addFieldset('dynamic_block_default', array(
                'legend' => $this->getMyHelper()->__( 'Default dynamic loaded blocks' ),
                'class'  => 'fieldset'
            ));
            
            $layoutFieldsetDefault->addField('cartsidebar', 'select', array(
                'name'      => 'cartsidebar',
                'label'     => $this->getMyHelper()->__('Cart Block (sidebar)').':',
            	'options'	=> array(
            					0 => $this->getMyHelper()->__('No'),
            					1 => $this->getMyHelper()->__ ( 'Yes' ))
            ));
            					
            $layoutFieldsetDefault->addField('pollsidebar', 'select', array(
                'name'      => 'pollsidebar',
                'label'     => $this->getMyHelper()->__('Poll Block (sidebar)').':',
            	'options'	=> array(
            					0 => $this->getMyHelper()->__('No'),
            					1 => $this->getMyHelper()->__ ( 'Yes' ))
            ));
            					
            $layoutFieldsetDefault->addField('comparesidebar', 'select', array(
                'name'      => 'comparesidebar',
                'label'     => $this->getMyHelper()->__('Compare Block (sidebar)').':',
            	'options'	=> array(
            					0 => $this->getMyHelper()->__('No'),
            					1 => $this->getMyHelper()->__ ( 'Yes' ))
            ));
            					
         
    		$layoutFieldsetUser = $form->addFieldset('dynamic_block_user', array(
                'legend' => $this->getMyHelper()->__( 'User dynamic loaded blocks (only for advanced developers).' ),
                'class'  => 'fieldset'
            ));
            
            $layoutFieldsetUser->addField('userblocks', 'textarea', array(
                'name'      => 'userblocks',
                'label'     => $this->getMyHelper()->__('Layout names of dynamic loaded blocks').':',
            	'after_element_html' => '<span><i>'.$this->getMyHelper()->__("Values separated by (',').").'</i></span>'
            ));
            
            $this->_addJsFixFieldset($form);

    	}
        
        /* bind data */
      	$form->setValues(Mage::getSingleton('mturbo/config_dynamicTransformer')->configToForm($config));
        $this->setForm($form);
       
        return parent::_prepareForm();
    }
    
	/**
	 * Method adds fieldset for patching layout to form.
	 * @param Varien_Data_Form $form
	 */
    private function _addLayoutPatchFieldset($form) {
    	
        /* @var $patchModel Artio_MTurbo_Model_LayoutPatch */
    	$patchModel = Mage::getSingleton('mturbo/layoutPatch');
    	
    	/* make fieldset */
    	$layoutFieldset = $form->addFieldset('layout_fieldset', array(
            'legend' => $this->getMyHelper()->__('Layout patch'),
            'class'  => 'fieldset'));
    	
    	/* button for patch Mage.php */
        $label = $patchModel->isPatched() ?
        	Mage::helper('mturbo')->__('Remove Layout Patch') :
        	Mage::helper('mturbo')->__('Apply Layout Patch');

        if ($patchModel->isWriteable()) {
        	
        	$layoutFieldset->addType('widget_button', Artio_MTurbo_Helper_Data::FORM_WIDGET_BUTTON);
        	$layoutFieldset->addField('laypatch_button', 'widget_button', array(
        	'name'		=> 'laypatch_button',
        	'label'		=> $label,
        	'style'		=> 'width:800px',
        	'onclick'	=> "setLocation('" . Mage::helper('adminhtml')->getUrl('*/*/layoutpatch') . "')",
        	'comment'	=> $this->getMyHelper()->__('This patch is required for dynamic loaded blocks. (Patched file: app/code/core/Mage/Core/Model/Layout.php)')));
        	
        } else {
        	
        	$layoutFieldset->addType('html_element', Artio_MTurbo_Helper_Data::FORM_HTML);
        	$scriptPath = $patchModel->getLayoutPath();
			$layoutFieldset->addField ( 'layoutstate', 'html_element', 
			array ('label' => '<h4>'.$this->getMyHelper()->__('Layout state').'</h4>',
				   'code' => '<span style="color:red">'.Mage::helper('mturbo')->__("Model '%s' is not writeable. Please change permission to write for patch.", $scriptPath).'</span>'));
		}
        
    	
    }	
    
    /**
     * Method adds fieldset for copying js file into all theme package.
     * @param Varien_Data_Form $form
     */
    private function _addJsFixFieldset($form) {
        
        $layoutFieldset = $form->addFieldset('layout_fieldset_watcher', array(
        	'legend' => $this->getMyHelper()->__('JavaScript Watcher (this control watches existing javascript in all your theme packages)'),
            'class'  => 'fieldset'
        ));
        
        /* @var $js Artio_MTurbo_Model_JsPatch */
        $js = Mage::getSingleton('mturbo/jsPatch');
      
        /* @var $themePackage Artio_MTurbo_Model_JsPatch */
        $layoutFieldset->addType('html_element', Artio_MTurbo_Helper_Data::FORM_HTML);
        foreach ($js->getAvailableThemePackages() as $i=>$themePackage) {
            
            if (!$themePackage->existsJs() && !$themePackage->makeJs()) {
                $html = '<span style="color:red">'.$this->getMyHelper()->__("Copying js file failed! Dynamic blocks won't work for this theme. Copy 'mturbo.js' from default theme, please!").'</span>';
            } else {
                $html = '<span style="color:green">'.$this->getMyHelper()->__("OK").'</span>';
            }
            
            $layoutFieldset->addField ('jsstate'.$i, 'html_element',
            array (
            	'label'	=> '<b style="font-size:10px">'.str_replace(Mage::getBaseDir().DS.'skin'.DS.'frontend', '', $themePackage->getJsPath()).'</b>',
                'code'  => $html)
            );
            
        }
     
        
    }

}