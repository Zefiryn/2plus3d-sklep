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
 * Admin page left menu
 *
 * @category   Artio
 * @package    Artio_MTurbo
 * @author     Artio Magento Team <info@artio.net>
 */
class Artio_MTurbo_Block_Adminhtml_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

	private $activeTab = '';
	
    public function __construct()
    {
        parent::__construct();
        $this->setId('page_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('mturbo')->__('Menu'));

        $request = Mage::getModel('core/url')->getRequest();
        $this->activeTab = $request->getParam('activeTab');
        
        $formKey = $request->getParam('form_key');
        if (!empty($formKey)) $this->activeTab = '';

    }
    
    public function addTab($tabId, $tab) {
    	parent::addTab($tabId, $tab);
   
    	if ($this->getId().'_'.$tabId == $this->activeTab) {
    		$this->setActiveTab($tabId);
    	}
    	
    }
    
}
