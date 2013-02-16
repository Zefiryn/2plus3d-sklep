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
 * Main container
 *
 * @category   Artio
 * @package    Artio_MTurbo
 * @author     Artio Magento Team <info@artio.net>
 */
class Artio_MTurbo_Block_Adminhtml_MTurbo extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_objectId = 'page_id';
        $this->_blockGroup = 'mturbo';
        $this->_controller = 'adminhtml';

        parent::__construct();
      
        $this->_removeButton('reset');
        $this->_removeButton('back');
        $this->_updateButton('save', 'label', Mage::helper('mturbo')->__('Save configuration'));
		$this->_addButton('upgrade', array(
            'label'     => Mage::helper('mturbo')->__('Upgrade to Full Version'),
            'onclick'   => "setLocation('" . Mage::helper('adminhtml')->getUrl('*/*/upgrade') . "')"), -1);
    }
    
    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return string
     */
    public function getHeaderText() {
    	return Mage::helper('mturbo')->__('M-Turbo Cache Management');
    }
    
    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action) {
        return Mage::getSingleton('admin/session')->isAllowed('cms/page/' . $action);
    }
}
