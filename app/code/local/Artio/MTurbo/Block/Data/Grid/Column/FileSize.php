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
class Artio_MTurbo_Block_Data_Grid_Column_FileSize
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
	implements Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Interface {
		
	 private $values = array('B'=>1, 'kB'=>1024, 'MB'=>1048576, 'GB'=>1073741824);
	 private $limit;
	 
	 public function _construct() {
	 	
	 	$config = Mage::getSingleton('mturbo/config');
	 	$this->limit  = $config->getMinimalPageSize();
	 	
	 }
	
	 public function render(Varien_Object $row) {
	 	
	 	$value = $row->getData($this->getColumn()->getIndex());
	 	
	 	$html = '';
	 	
	 	/* show only if file size is greater than 0 */
	 	if ($value > 0) {
	 		$color = ($value < $this->limit) ? 'red' : 'green';
        	$formatValue = Mage::helper('mturbo/functions')->format_file_size($value);
        	$html = '<span style="color:'.$color.'">'.$formatValue.'</span>';
	 	}

        return $html;

    }
    
}                           