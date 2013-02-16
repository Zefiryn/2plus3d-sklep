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
 * @author     Artio Magento Team (info@artio.net)
 */
class Artio_MTurbo_Block_Ajax extends Mage_Core_Block_Template
{

    public function __construct($attributes=array())
    {    
        parent::__construct($attributes);
      	$this->setTemplate('mturbo/ajax');
    }
    
    public function __call($method, $args) {
    	// do not, prevents call undefined methods 
    }
    
    public static function __callStatic($name, $arguments) {
     	// do not, prevents call undefined methods 
    }

    protected function _toHtml() {
    	
    	$id 	 = $this->getData('ajax_identifier');
    	$clearId = str_replace(".", "_", $id);
    	
    	$html = "<div id=\"".$id."\"></div>
		<script type=\"text/javascript\">
		//<![CDATA[
			function fillBlock".$clearId."() {
			    if (typeof(mturboloader)!='undefined') {
				    mturboloader;
					if (mturboloader.complete) {
						document.getElementById('".$id."').innerHTML = mturboloader.getBlock('".$id."');
    				} else {
    					setTimeout('fillBlock".$clearId."()', 100);
    				}
    			}
    		}
			fillBlock".$clearId."();
		//]]>
		</script>";
    	
    	return $html;
    }
    
}
