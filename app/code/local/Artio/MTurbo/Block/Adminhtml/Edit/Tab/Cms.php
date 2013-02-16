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
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Artio_MTurbo_Block_Adminhtml_Edit_Tab_Cms
    extends Artio_MTurbo_Block_Adminhtml_Edit_Tab_Abstract
{
	
	public function __construct() {
		parent::__construct();
		$this->setId('cms_section');
		$this->_title = $this->getMyHelper()->__('CMS');
	}
	
    protected function _prepareForm() {
    	
    	$config = Mage::getSingleton('mturbo/config');			
    	
    	$form = new Varien_Data_Form();
    	
    	/* fieldset for automatic refresh */
    	$layoutFieldsetRefresh = $form->addFieldset('products_refresh_fieldset', array(
            'legend' => $this->getMyHelper()->__( 'Automatic refresh settings' ),
            'class'  => 'fieldset'
        ));
        
        $layoutFieldsetRefresh->addField('add_newly_cms_to_select', 'select', array(
            'name'      => 'add_newly_cms_to_select',
            'label'     => $this->getMyHelper()->__('Added newly cms to select').':',
        	'options'	=> array(
        					0 => $this->getMyHelper()->__('No'),
        					1 => $this->getMyHelper()->__ ( 'Yes' ))));

        $layoutFieldsetRefresh->addField('refresh_cms', 'select', array(
            'name'      => 'refresh_cms',
            'label'     => $this->getMyHelper()->__('Enable automatic refresh after save CMS pages').':',
        	'options'	=> array(
        					0 => $this->getMyHelper()->__('No'),
        					1 => $this->getMyHelper()->__ ( 'Yes' ))));
        					
       
        /* fieldset for tree */
    	$layoutFieldset = $form->addFieldset('products_fieldset', array(
            'legend' => $this->getMyHelper()->__( 'Select CMS pages to cache' ),
            'class'  => 'fieldset'
        ));
        
        /* tree */
     	$layoutFieldset->addType('cms_tree', Artio_MTurbo_Helper_Data::FORM_CMS_TREE);
        $layoutFieldset->addField('cms_tree', 'cms_tree', array(                    
          'name'   => 'cms_tree',
          'with' => $config->getCmsPagesWithStoresAsArray(),
          'without' => $config->getCmsPagesWithoutStoresAsArray()
      	));


        /* bind data */
      	$form->setValues($config->getData());
        $this->setForm($form);
       
        return parent::_prepareForm();
    }

}