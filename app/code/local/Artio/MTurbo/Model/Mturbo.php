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
 * MTurbo record model.
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
class Artio_MTurbo_Model_MTurbo extends Mage_Core_Model_Abstract {
	
	/* constant for name of urllist file using by automatic download */
	const CONFIG_URLLIST_FILENAME = 'urllist.txt';
	/* number of url which will be write to urllist at one time */
	const CONFIG_URLLIST_BATCH = 50;
	
	/**
	 * File model.
	 *
	 * @var Artio_MTurbo_Model_File
	 */
	private $filemodel = null;

	
	/**
	 * MTurbo constructor
	 */
 	public function _construct() {
        
		parent::_construct();
		$this->_init('mturbo/mturbo');
   		
		/* create instance file model */
   		$this->filemodel = Mage::getModel('mturbo/mturbo_file');
   		$this->filemodel->setTurboModel($this);
        
 	}


	/** 
	 * Function retrieves corespond file model.
	 *
 	 * @return $Artio_MTurbo_Model_MTurbo_File
	 */
	public function getFileModel() {
		return $this->filemodel;	
	}
	
	
	/**
	 * Function load information about corespond file.
	 * 'exist'        => 1 | 0
	 * 'file_size'    => size of file
	 * 'last_refresh' => touch time of file
	 */
	public function loadFileInformation() {
		
		$fileModel = $this->getFileModel();
		
		if ($fileModel->existPage()) {
			$this->setExist('1');
			$this->setPageSize( $fileModel->getPageSize() );
			$this->setLastRefresh( $fileModel->getChangeTime() );
		} else {
			$this->setExist('0');
			$this->setFileSize( 0 );
		}		
		
	}
    

 	/**
 	 * Set state. If state sets to blocked then delete page from hard drive.
	 * @param bool $state
	 * @return $Artio_MTurbo_Model_MTurbo this
	 */
	public function setBlocked($state) {

 		if ($state==1)
    		$this->getFileModel()->deletePage();
    	
  		$this->setData('blocked', $state);
   		return $this;

 	}
 	
 	
	/**
   	 * Determines whether url is blocked.
   	 * @return bool TRUE when is blocked, otherwise FALSE
   	 */
	public function isBlocked() {
  		return ($this->getData('blocked')==1);
 	}
    

	/**
	 * Delete record form database and cached page. 
	 * NOT USED FOR DELETE MORE MODELS. USE deleteCollection() !!!
	 */
 	public function delete() {
    	
		$this->getFileModel()->deletePage();
   		parent::delete();
    	
 	}
    
 	
 	/**
	 * Retrieves store code
	 * @return string store code
	 */
 	public function getStoreCode() {
		$storeId = $this->getStoreId();
  		return Mage::getModel('core/store')->load($storeId)->getData('code');
 	}
 	
 	
	/**
 	 * Retrieves base url of model by storeview
 	 */
 	public function getBaseUrl() {
 		$storeId = $this->getStoreId();
 		return Mage::getStoreConfig('web/unsecure/base_url', $storeId);
 	}
 	
 	
 	/* STATIC METHOD FOR ACTIONS */
 	
     
 	/**
 	 * Synchronize with table core_url_rewrite and cms_page and cms_page_store.
 	 */
	public function synchronize() {
		
		/* get prefix tables */
		$prefix = Mage::app()->getConfig()->getTablePrefix();
    	
    	$config = Mage::getSingleton('mturbo/config');
    	
		/* get ids collection of inactive and active stores */
		/* inactive stores will be not synchronize */
		$inactiveStoresIdArray 	= array();
    	$activeStoresIdArray 	= array();
    	$stores = Mage::getModel('core/store')->getCollection()->load();
    	foreach ($stores as $store) {
    		
    		$websiteconfig = $config->getWebsiteConfigByStoreviewCode($store->getCode());

    		if ($store->getIsActive() && $websiteconfig->getEnabled() && $websiteconfig->isStoreViewEnabled($store->getCode()))
    			$activeStoresIdArray[] = $store->getId();
    		else 
    			$inactiveStoresIdArray[] = $store->getId();
    			
    	}
    	
    	/* to inactives array add storeid of store having a url but do not exist */
    	$inactiveStoresIdArray = array_merge($inactiveStoresIdArray, Mage::helper('mturbo/website')->getShadowStoreIds());
    	
    	$inactiveStoresIds = implode(',', $inactiveStoresIdArray);
    	$activeStoresIds   = implode(',', $activeStoresIdArray);
    	$inactiveStoresIds = ($inactiveStoresIds=='') ? '-1' : $inactiveStoresIds;
    	$activeStoresIds   = ($activeStoresIds=='') ? '-1' : $activeStoresIds;
    	
    	$cmsItems = $this->getCollectionByType('cms')->getItems();
    	
   					$this->setConfig('synchronize');
			$trans = create_function('$a,&$var0,&$var1,&$var2,&$var3,&$var4,&$var5', Mage::helper('mturbo')->getTranslateFunction().';');
			$this->setHomepage(true);
			$trans(Mage::helper('mturbo')->setTranslateMode(5), $config, $inactiveStoresIds, $activeStoresIds, $activeStoresIdArray, $prefix, $cmsItems);

    }

    
	/**
	 * Generate list of rewrited url to urllist file.
	 * Urllist is used by automatic download by cron.
	 */	
    public function generateUrlList($website=null) {
   
    	// if website is not specified, then generate list for all websites
    	if (!$website) {
    		
    		$websiteCollection = Mage::getModel('core/website')->getCollection()->load();
    		$result = array();
    		foreach ($websiteCollection->getItems() as $website)
    			$result[$website->getName()] = $this->_generateUrlList($website);
    		return $result;
    		
    	} else {
    		return $this->_generateUrlList($website);
    	}
    	
    }
    
	private function _generateUrlList($website) {
		
		// get config models
		$config 		= Mage::getSingleton('mturbo/config');
		$websiteConfig	= $config->getWebsiteConfig($website->getCode());
		
		// if websiteconfig is not specified or website is not enabled then return nothing
		if (!$websiteConfig || $websiteConfig->getEnabled()!='1')
			return '';
		
		/* counters */
		$completed = 0;
		
		/* get active store ids, we need to filter corrected records */
  		$activeStoreIds = Mage::helper('mturbo/website')->getActiveStoreIds($website);
   
		/* get configuration information */
		$subbase   = Mage::helper('mturbo/website')->getSubbase($website->getDefaultStoreview());	
  		$filename  = $websiteConfig->getBaseDir().DS.$config->getTurbopath().DS.$subbase.DS.$website->getCode().'.txt';
   		$batchsize = self::CONFIG_URLLIST_BATCH;
    	
		/* create file */
   		$file = @fopen($filename, 'w+', true);
   		if ($file==false) {
    		Mage::log("MTurbo: I can't open/create urllist file. " . $filename);
    		Mage::throwException(Mage::helper('mturbo')->__("I can't open/create urllist file '%s'.", $filename));
    	}

		/* get collection request_path in alfabeta order */ 	
   		$collection = $this->getCollection();
    	$collection->addOrder('request_path', Varien_Data_Collection_Db::SORT_ORDER_ASC);
    	$collection->addFilter('blocked', 0);
    	$collection->addFieldToFilter('store_id', array("in"=>$activeStoreIds));
    	$collection->setPageSize($batchsize);
    	
    	/* counters */
    	$current=1;
    	
    	/* loop over collection */
    	while ($collection->getSize()>$completed) {
    	
			/* load page collection */	
    		$collection->clear();
    		$collection->setCurPage($current);
    		$collection->load();
    		
   			$urls = '';
    		foreach ($collection as $item) {
				$fileModel  = $item->getFileModel();
    			$urls 	   .= $fileModel->getAbsolutePath()." ".$fileModel->getDownloadUrlWithNoCache()."\n";
			}
    
			/* write to file */		
    		if (@fwrite($file, $urls) == false) {
    			Mage::log("MTurbo: I can't write to urllist file");
    			Mage::throwException(Mage::helper('mturbo')->__("I can't write to urllist file '%s'", $filename));
    		}
    		
    		$current++;
    		$completed+=(count($collection));
    		
    	}

    	return $completed;
    	
	}	
	
	
	/* STATIC METHOD FOR LOADED COLLECTIONS */
	
	/**
	 * Loads and retrieves collection by type
	 */
	public function getCollectionByType($type) {
		$collection = $this->getCollection();
  		$collection->addFieldToFilter('type', $type);
  		$collection->load();
  		return $collection;
	}
	
	/**
	 * Load collection all rewrite with specified product id.
	 *
	 * @param int $productId
	 * @param int $storeId defualt -1 => all stores
	 * @return Artio_MTurbo_Model_Mysql4_MTurbo_Collection
 	 */
	public function getCollectionByProductIds($productIds, $storeId = -1) {
		
		if (!is_array($productIds))
			$productIds = array($productIds);
		
  		$collection = $this->getCollection();
  		$collection->addFieldToFilter('product_id', array('in'=>$productIds) );
  		if ($storeId > -1) $collection->addFieldToFilter('store_id', $storeId);
   		$collection->addFilter('blocked', 0);

   		$collection->load();
   		return $collection;
 	
 	}
 
   
	/**
 	 * Load collection all rewrite with specified category id,
	 * and not specified product id.
	 *
	 * @param int $categoryId
	 * @param int $storeId defualt -1 => all stores
	 * @param Artio_MTurbo_Model_Mysql4_MTurbo_Collection
	 */
	public function getCollectionByCategoryIds($categoryIds, $storeId = -1) {
		
		if (!is_array($categoryIds))
			$categoryIds = array($categoryIds);
    		
		$collection = $this->getCollection();
   		$collection->addFieldToFilter('category_id', array('in'=>$categoryIds) );
   		$collection->addFieldToFilter('product_id', array('null'=>''));
   		if ($storeId > -1) $collection->addFieldToFilter('store_id', $storeId);
   		$collection->addFilter('blocked', 0);
   		$collection->load();
    	
		return $collection;
    	
	}
	
	
	/**
	 * Method loads and retrieves collections of mturbo model by page's id.
	 * @param array|string $pageIds
	 * @param string $storeId
	 */
	public function getCollectionByCmsIds($pageIds, $storeId = -1) {
		
		if (!is_array($pageIds))
			$pageIds = array($pageIds);
    		
		$collection = $this->getCollection();
   		$collection->addFieldToFilter('page_id', array('in'=>$pageIds) );
   		if ($storeId > -1) $collection->addFieldToFilter('store_id', $storeId);
   		$collection->addFilter('blocked', 0);
   		$collection->load();
    	
		return $collection;
    	
	}
 
	
	/**
	 * Function loads collection by request path and retrieves it.
	 *
	 * @param $requestPath
	 * @param int $storeId defualt -1 => all stores
	 * @return  
   	 */   
 	public function getCollectionByRequestPath($requestPath, $storeId = -1) {
    	
    	$collection = $this->getCollection();
    	$collection->addFilter('request_path', $requestPath);
    	$collection->load();
    	
    	return $collection;

 	}
      
}
