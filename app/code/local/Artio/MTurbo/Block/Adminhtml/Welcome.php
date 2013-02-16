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
 * Welcome page. Showed at first executed MTurbo Management.
 *
 * @category   Artio
 * @package    Artio_MTurbo
 * @author     Artio Magento Team <info@artio.net>
 */
class Artio_MTurbo_Block_Adminhtml_Welcome extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct() {
    	
        $this->_objectId = 'page_id';
        $this->_blockGroup = 'mturbo';
        $this->_controller = 'adminhtml';
		$this->_mode = 'welcome';

        parent::__construct();
      
        $this->_removeButton('reset');
        $this->_removeButton('back');
        $this->_removeButton('save');
        
    }
 
    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return string
     */
    public function getHeaderText() {
    	return Mage::helper('mturbo')->__('Magento Turbo Cache Management');
    }
    
    protected function _afterToHtml($html) {
    	return '<div style="width:50%;margin:0 auto;font-weight:bold;">'.$html.'</div>';
    }
    
}
