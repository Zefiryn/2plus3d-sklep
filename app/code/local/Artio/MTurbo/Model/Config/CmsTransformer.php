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
 * @category    Artio
 * @package     Artio_MTurbo
 * @copyright   Copyright (c) 2010 Artio (http://www.artio.net)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * The model contains function for preparing configuration data of selected cms pages for 
 * administration form and back.
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
class Artio_MTurbo_Model_Config_CmsTransformer extends Varien_Object
{
	
	/**
	 * Method transform form data to config and fill it.
	 * @param Artio_MTurbo_Model_Config $config
	 * @param array $formData data from form
	 */
	public function extractData($config, $formData=array()) {
		
		$cms  = array();
		
		/* foreach data */
		foreach ($formData as $key=>$value) {
			
			$keys = array();

			/* key for selected pages */
			if (preg_match('/^cms_tree_([0-9]+_[0-9]+)$/', $key, $keys)) {
				if (count($keys)==2)
					$cms[] = $keys[1];
			}
			
		}
		
		$config->setCmsPages(implode(",", $cms));
		
	}
	
	/**
     * Method transform data for administration form and retrieves as array.
     * @param Artio_MTurbo_Model_Config $config
     * @return array transformed data
     */
    public function configToForm($config) {
    	
    	/* result is empty */
    	$result = array();
    	
        foreach ($config->getCmsPagesWithStoresAsArray() as $cms) {
    		$result['cms_tree_'.$cms] = '1';
    	}
    	
    	/* return result */
    	return $result;
    	
    }
	
}