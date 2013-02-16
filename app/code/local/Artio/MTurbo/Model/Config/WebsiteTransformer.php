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
 * The model contains function for preparing configuration data of website for 
 * administration form and back.
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
class Artio_MTurbo_Model_Config_WebsiteTransformer extends Varien_Object
{
	
	/**
	 * Method transform form data to config and fill it.
	 * @param Artio_MTurbo_Model_Config $config
	 * @param array $formData data from form
	 */
	public function extractData($config, $formData=array()) {
		
		/* foreach data */
		foreach ($formData as $key=>$value) {

			$keys = array();
			
			/* key for store enabled */
			if (preg_match('/^website-([_a-zA-Z0-9]+)-store-([_a-zA-Z0-9]+)$/', $key, $keys)) {
				if (count($keys)==3)
					$config->getWebsiteConfig($keys[1])->setOneStoreViewEnabled($keys[2], $value);
			}
			
			/* key for website configuration */
			else if (preg_match('/^website-([_a-zA-Z0-9]+)-(.+)$/', $key, $keys)) {
				if (count($keys)==3)
					$config->getWebsiteConfig($keys[1])->setData($keys[2], $value);
			}
			
		}
		
	}
	
	/**
     * Method transform data for administration form and retrieves as array.
     * @param Artio_MTurbo_Model_Config $config
     * @return array transformed data
     */
    public function configToForm($config) {
    	
    	/* result is empty */
    	$result = array();
    	
    	/* for each website */
    	$websiteCollection = Mage::getModel('core/website')->getCollection()->load();
    	foreach ($websiteCollection as $website) {
    		
    		/* get settings for website */
    		$websiteConfig = $config->getWebsiteConfig($website->getCode());
    		
    		if ($websiteConfig) {
    		
    			/* bind data settings */
    			$result['website-'.$website->getCode().'-enabled'] 	    = $websiteConfig->getEnabled();
    			$result['website-'.$website->getCode().'-base_dir']     = $websiteConfig->getBaseDir();
          $result['website-'.$website->getCode().'-server_name']  = $websiteConfig->getServerName();
    		
    			/* for each storeview determine whethere enabled is */
    			$enabledStoreview = $websiteConfig->getEnabledStoreviewsAsArray();
    			foreach ($website->getStores() as $store) 
    				if ($store->getIsActive()) {
    					if (in_array($store->getCode(), $enabledStoreview))
    						$result['website-'.$website->getCode().'-store-'.$store->getCode()] = '1';
    					else 
    						$result['website-'.$website->getCode().'-store-'.$store->getCode()] = '0';
    				}
    						
    		}
    		
    		break;
    		
    	}
    	
    	/* return result */
    	return $result;
    	
    }
	
}