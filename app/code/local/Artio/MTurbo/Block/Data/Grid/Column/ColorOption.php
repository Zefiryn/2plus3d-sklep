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
 * @category   Varien
 * @package    Varien_Data
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Form button element
 *
 * @category   Artio
 * @package    Artio_MTurbo
 * @author     Artio Magento Team <info@artio.net>
 */
class Artio_MTurbo_Block_Data_Grid_Column_ColorOption
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
	implements Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Interface {
		
	public function __construct($attrs=array()) {
		parent::__construct();
		$this->setColors($attrs);	
	}
		
	public function render(Varien_Object $row) {
	 	
	 	$value  = $row->getData($this->getColumn()->getIndex());
	 	$colors = $this->getColors(); 	
	 	$options = $this->getColumn()->getOptions();

	 	$html = '';
	 	
	 	if (isset($options[$value])) {
        	$html = '<span style="cursor:pointer;color:'.(isset($colors[$value])?$colors[$value]:'gray').'">'.$options[$value].'</span>';
	 	}
	 	
        return $html;

    }
    
}                           
