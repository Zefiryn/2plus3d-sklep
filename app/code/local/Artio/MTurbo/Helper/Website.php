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
 * Helper contains varioused features for Magento website and Magento store models.
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
class Artio_MTurbo_Helper_Website extends Mage_Core_Helper_Abstract
{
	
	/**
	 * Function retrieves extension stored in Magento configuration.
	 * Default in category extension.
	 * @param Mage_Core_Model_Store $store
	 */
	public function getExtension($store) {
		if (!isset($store) || !$store) return Mage::getStoreConfig('catalog/seo/category_url_suffix');
		$extension = Mage::getStoreConfig('catalog/seo/category_url_suffix', $store->getId());
		if (isset($extension) && ($extension!='') && ($extension[0]=='.')) $extension = '\\'.$extension;
		return $extension;
	}
	
	
	/**
	 * Function retrieves subbase for Magento store.
	 * @param Mage_Core_Model_Store $store
	 */
	public function getSubbase($store) {
		
		/* make url without http:// or https:// */
		$completeUrl = Mage::getStoreConfig('web/unsecure/base_url', $store);
		$completeUrl = str_replace('https://', '', $completeUrl);
		$completeUrl = str_replace('http://', '', $completeUrl);
		
		/* explode to array, array[0] is host */
		$urlArray = explode(DS, $completeUrl);
			
		/* remove host */
		array_shift($urlArray);
			
		/* make back to path */
		$path = implode(DS, $urlArray);
		
		return $path;
		
	}

	/**
	 * Retrieve supposed server name for storeview specified by its code.
	 * @param string $storeCode
	 * @return string server name
	 */
    public function getServerName($storeCode) {
    
      $baseUrl = Mage::getStoreConfig('web/unsecure/base_url', $storeCode);
      
      $baseUrl = str_ireplace('http://',          '', $baseUrl);
      $baseUrl = str_ireplace('https://',         '', $baseUrl);
      $baseUrl = str_ireplace('www.',             '', $baseUrl);
      $baseUrl = str_ireplace('/index.php/admin', '', $baseUrl);
      $baseUrl = str_ireplace('/index.php',       '', $baseUrl);
      $baseUrl = str_ireplace(Mage::helper('mturbo/website')->getSubbase($storeCode), '', $baseUrl);
      $baseUrl = str_ireplace('/',                '', $baseUrl);

      return $baseUrl;

    }
	
    /**
     * Get ids of the stores having a url but do not exist.
     * 
     * @return array
     */
    public function getShadowStoreIds() {
        
        try {
            
            $prefix = Mage::app()->getConfig()->getTablePrefix();
            
            $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
            
            $query = "
            	SELECT 
            		DISTINCT `r`.`store_id` AS \"id\"
    			FROM 
      				`${prefix}core_url_rewrite` `r`
      				LEFT JOIN `${prefix}core_store` `s` ON (`r`.`store_id`=`s`.`store_id`)
    			WHERE
     	 			`s`.`store_id` IS NULL";
           
            $ids = $connection->fetchAll($query);
            
            $result = array();
            foreach ($ids as $id)
                $result[] = $id['id'];
                
            return $result;
        
        } catch (Exception $e) {
        
            Mage::log("M-Turbo:".$e->getMessage());
            Mage::logException($e);

            return array();
            
        }
    }
	
	
	/**
	 * Function retrieves actived and enables stores for website.
	 * If website is null, retrieves stores for all website
	 * @param $website 
	 * @return array
	 */
	public function getActiveStoreIds($website=null) {
		
		if (!$website) {
			$result = array();
			$coll 	= Mage::getModel('core/website')->getCollection()->load();
			foreach ($coll->getItems() as $website) {
				$result = array_merge($result, $this->_getActiveStoreIds($website));
			}
			return $result;
		} else {
			return $this->_getActiveStoreIds($website);
		}
		
	}
	
	private function _getActiveStoreIds($website) {
		
		if (!$website)
			return array();
			
		$config = Mage::getSingleton('mturbo/config')->getWebsiteConfig($website->getCode());
		
		if (!$config || !$config->getEnabled())
			return array();
		
		$result = array();
		$coll	= $website->getStoreCollection();
		foreach ($coll as $store) {
			$code = $store->getCode();
			$enbl = $config->isStoreViewEnabled($code);
			if ($store->getIsActive() && $enbl)
				$result[] = $store->getId();
		}
			
		return $result;
		
	}
	
	
}
