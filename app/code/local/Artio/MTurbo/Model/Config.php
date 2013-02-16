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
 * The model holds all configuration information.
 * It is appropriate to create as Singleton.
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
class Artio_MTurbo_Model_Config extends Varien_Object
{
	
	const CONFIG_XML_PATH_DOWNLOAD_MODEL_PATH = 'crontab/jobs/mturbo_mturbo/run/model';
	const CONFIG_XML_PATH_DOWNLOAD_MODEL_VALUE = 'mturbo/observer::automaticDownload';
	
	
	/**
	 * Map associated private member name to xml configuration key
	 * @var array
	 */
	private static $configArrayMap = NULL;

	
	/**
	 * Function retrieve associated array 'name'=>'config key'.
	 */
	public function getConfigArrayMap() {
	
		if (is_null(self::$configArrayMap)) {
		
			self::$configArrayMap = array(
			
				'preview_categories' 				=> 'mturbo/previewcats',
				'product_categories'	  			=> 'mturbo/productcats',
				'cms_pages'							=> 'mturbo/cmspages',
				'turbopath'							=> 'mturbo/turbopath',
				'download_method'					=> 'mturbo/downloadmethod',
				'automatic_download'				=> 'mturbo/automaticdownload',
				'automatic_download_time'			=> 'crontab/jobs/mturbo_mturbo/schedule/cron_expr',
				'last_time_of_automatic_download'	=> 'mturbo/lastdownload',
				'enabled_htaccess_backup'			=> 'mturbo/htaccessbackup',
				'number_of_htaccess_backups'		=> 'mturbo/numberhtaccessbackups',
				'minimal_page_size'					=> 'mturbo/minimalpagesize',
				'download_id'						=> 'mturbo/downloadid',
				'add_newly_category_to_select'		=> 'mturbo/newcategory',
				'add_newly_product_to_select'		=> 'mturbo/newproduct',
				'add_newly_cms_to_select'			=> 'mturbo/newcms',
				'refresh_category'					=> 'mturbo/refreshcategory',
				'refresh_parents_for_category'		=> 'mturbo/refreshparentcategory',
				'refresh_product'					=> 'mturbo/refreshproduct',
				'refresh_parent_of_product'			=> 'mturbo/refreshparproduct',
				'refresh_parents_for_product'		=> 'mturbo/refreshparentproduct',
				'refresh_cms'						=> 'mturbo/refreshcms',
				'dynamic_blocks'					=> 'mturbo/dynamicblocks',
				'firstconfig'						=> 'mturbo/firstconfig',
				'interpret'							=> 'mturbo/interpret'
			);

		}

		return self::$configArrayMap;	

	}
	
	/**
   	 * Constructs configuration model.
	 * Currently loaded configuration from the database.
	 */
	public function __construct() {
		parent::__construct();
		$this->load();
	}


	/**
	 * Array of singletons of configuration for websites.
	 * ('websitecode'=>'configuration')
	 * @var array
	 */
	private $websitesConfiguration = array();

	
	/**
	 * Save key => id, prevent duplicate entry during saving.
	 * @var array
	 */
	private $pathids = array();
	
	
	/**
	 * Retrieves website configuration.
	 * @param string $websiteCode code of website
	 * @return Artio_MTurbo_Model_Config_Website
	 */
	public function getWebsiteConfig($websiteCode) {
		
		if (!isset($this->websitesConfiguration[$websiteCode]))
			$this->websitesConfiguration[$websiteCode] = 
				Mage::getModel('mturbo/config_website')
					->setWebsiteCode($websiteCode)
					->load();
					
		return $this->websitesConfiguration[$websiteCode];
		
	}
	
	
	/**
	 * Retrieve website configuration by storeview codes.
	 * @param string $storeviewCode
	 * @return Artio_MTurbo_Model_Config_Website
	 */
	public function getWebsiteConfigByStoreviewCode($storeviewCode) {
		
		$store = Mage::getModel('core/store')->load($storeviewCode);
		if ($store)
			return $this->getWebsiteConfig($store->getWebsite()->getCode());
		else
			return null;
		
	}
	
	
	/**
	 * Method retrieves codes of all enabled stores.
	 */
	public function getAllEnabledStores() {
		$result = '';
		$coll 	= Mage::getModel('core/website')->getCollection()->load();
		foreach ($coll->getItems() as $website) {
			$config = $this->getWebsiteConfig($website->getCode());
			if ($config && $config->getEnabled()=='1')
				$result .= ','.$config->getEnabledStoreviews();
		}
		return explode(",", $result);
	}
	
	/**
	 * Retrieves ids of selected categories as array.
	 * @return array
	 */
	public function getPreviewCategoriesAsArray() {
		return $this->getPreviewCategories()!='' ? explode(",", $this->getPreviewCategories()) : array();
	}
	
	
	/**
	 * Retrieves ids of selected product categories as array.
	 * @return array
	 */
	public function getProductCategoriesAsArray() {
		return $this->getProductCategories()!='' ? explode(",", $this->getProductCategories()) : array();
	}
	
	
	/**
	 * Retrieves ids of selected cms pages and the id of 
	 * coresponded storeview code as array.
	 * Element of array has format pageid_storeid
	 * @return array
	 */
	public function getCmsPagesWithStoresAsArray() {
		return explode(",", $this->getCmsPages());
	}
	
	
	/**
	 * Retrieves ids of cms pages, which has at least one store view mutation checked.
	 * @return array
	 */
	public function getCmsPagesWithoutStoresAsArray() {
		$result = array();
		foreach ($this->getCmsPagesWithStoresAsArray() as $pageWithStore) {
			$matches = array();
			preg_match_all('/([0-9]+)_([0-9]+)/', $pageWithStore, $matches);
			if (count($matches)==3)
				if (isset($matches[1][0]))
					$result[] = $matches[1][0];
		}
		return array_unique($result);
	}
	
	
	/**
	 * Retrieves layout names of selected dynamic blocks as array.
	 * @return array
	 */
	public function getDynamicBlocksAsArray() {
		return explode(",", $this->getDynamicBlocks());
	}
	
	
	/**
	 * Set preview categories
	 * @param string|array $categories
	 */
	public function setPreviewCategories($categories) {
		$this->setData('preview_categories', implode(",", Mage::helper('mturbo/functions')->make_unique_array($categories)));
		return $this;		
	}

	
	/**
	 * Set product categories
	 * @param string|array $categories
	 */
	public function setProductCategories($categories) {
		$this->setData('product_categories', implode(",", Mage::helper('mturbo/functions')->make_unique_array($categories)));
		return $this;		
	}

	
	/**
	 * Set cms pages.
	 * @param string|array $categories
	 */
	public function setCmsPages($pages) {
		$this->setData('cms_pages', implode(",", Mage::helper('mturbo/functions')->make_unique_array($pages)));
		return $this;		
	}


	/**
	 * Set turbo path
	 * @param string $path
	 */
	public function setTurbopath($path) {
	
		$len = strlen($path);
		if ($path[$len-1]=='/')
			$path = substr($path, 0, $len-1);

		$this->setData('turbopath', $path);	

	}
	
	
	/**
	 * Function set download method. 
   	 * If download method is unknow, then function set default method
   	 *
   	 * @param string code of method
   	 */
	public function setDownloadMethod($method) {

		$methodsFactory = Mage::getSingleton('mturbo/downloadMethodsFactory');

		/* checks method */
		if ($methodsFactory->exists($method))
			$this->setData('download_method', $method);
		else
			$this->setData('download_method', $methodsFactory->getDefaultMethod());
			
		return $this;
 
	}
	
	
	/**
     * Function transform download time in classic format to cron format, when necessary
     *
     * @param $value download time in format array([0]=>hours, [1]=>minutes);
     * @return string download time in cron format
     */
	public function setAutomaticDownloadTime($value) {
		if (is_array($value)) {
			$hours = (int)$value[0];
			$minutes = (int)$value[1];
			$this->setData('automatic_download_time', $minutes . ' ' . $hours . ' * * *');
		} else {
			$this->setData('automatic_download_time', $value);
		}
		return $this;
	}

	
	/**
	 * Load attributes from core_config_data
	 * @return Artio_MTurbo_Model_Config this
	 */
	public function load() {
		
		/* load from configuration table all records contains mturbo */		
		$config = Mage::getModel('core/config_data');
		
		$collection = $config->getCollection();
		$collection->addFieldToFilter('path', array('like'=>'%mturbo%'));
		$collection->load();

		/* load all private members and flip it */			
		$mydata = array_flip($this->getConfigArrayMap());

		/* foreach records from configuration table */
		foreach ($collection as $object) {

			$key = $object->getPath();				
			if (array_key_exists($key, $mydata)) {
				$func = 'set'.str_replace(" ", "", ucwords(str_replace("_", " ", $mydata[$key])));
				$this->$func($object->getValue());
			}
			
			/* save pathid, prevent duplicate entry during saving */
			$this->pathids[$key] = $object->getData('config_id'); 
				
		}
		
		return $this;
		
	}
	
	
	/**
	 * Function saves value from model to database.
   	 *
   	 * @param array attributes to save
	 */
	public function save($attributes=array()) {
		
		/* get resource transaction model */
		$saveTransaction = Mage::getModel('core/resource_transaction');

		/* get configuration map */
		$mydata = $this->getConfigArrayMap();
		
		/* save attributes in argument using setter */
		foreach ($attributes as $key=>$value) {
			if (array_key_exists($key, $mydata)) {
				
				/* check whether turbopath was changed, if yes then in validate execute correcten action */
				if ($key=='turbopath' && $this->getTurbopath()!='' && $this->getTurbopath()!=$attributes['turbopath'])
					$this->setData('old_turbopath', $this->getTurbopath());
				
				$func = 'set'.str_replace(" ", "", ucwords(str_replace("_", " ", $key)));
				$this->$func($value);
			}
		}

		/* saves to transaction all configuration values */
		foreach ($mydata as $name=>$key) {

			/* save object to transaction */
			$dataObject = Mage::getModel('core/config_data');
			$dataObject->setPath($key);
			$dataObject->setValue($this->getData($name));			
			/* set id, prevent duplicate entry */
			if (array_key_exists($key, $this->pathids)) {
				$dataObject->setId( $this->pathids[$key] );
			}

			$saveTransaction->addObject($dataObject);

		}
		
		/* validate input data */
		$this->_validate();
		$this->_changePath();
		
		/* save into database */
		$saveTransaction->save();
		
		/* save slaved website configuration */
		$websites = Mage::getModel('core/website')->getCollection()->load()->getItems();
		foreach ($websites as $website) {
			$this->getWebsiteConfig($website->getCode())->save();
		}
	
		return $this;
	
	}
	
	/**
	 * Function change path. It is executed when user change turbopath or 
	 * website base dir in m-turbo configuration (except first executed during installation).
	 */
	private function _changePath() {
		
		foreach ($this->websitesConfiguration as $websiteConfig) {
			if ($websiteConfig->getEnabled()=='1') {
		
				$oldtp = $this->getData('old_turbopath');
				$oldbd = $websiteConfig->getData('old_basedir');
				if (($oldtp!=='') || ($oldbd!=='')) {
					
					$newtp = $this->getTurbopath();
					$newbd = $websiteConfig->getBaseDir();
					
					@rename($oldbd.DS.$oldtp, $newbd.DS.$newtp);
					Mage::getModel('mturbo/htaccess')
						->setWebsiteCode($websiteConfig->getWebsiteCode())
						->rebuildHtaccess();
				
				}
			}
		}
		
	}
	

	/**
	 * Function validates user's input.
	 * Throws exception, when inpus is not valid.
	 */
	private function _validate() {	

		$path = $this->getTurbopath();

		if (!$this->_isPathCorrect($path))
			Mage::throwException(Mage::helper('mturbo')->__("Path '%s' is not correct.", $path));
		if (!$this->_isPathPossible($path))
			Mage::throwException(Mage::helper('mturbo')->__("Path '%s' is not usable. This path is used by system Magento, please enter other path.", $path));
	
		$paths = array();
		foreach ($this->websitesConfiguration as $websiteConfig) {
			if ($websiteConfig->getEnabled()=='1') {

				$path = $websiteConfig->getBaseDir();

				if (!$this->_isPathCorrect($path))
					Mage::throwException(Mage::helper('mturbo')->__("Path '%s' is not correct.", $path));

				
			}
		}
		
	}
	
	/**
	 * Check general form of input path.
	 */
	private function _isPathCorrect($path) {
		return preg_match('/^([a-zA-Z]\:\/)?([a-zA-Z0-9\._\/\-\\\\])*$/', $path);
	}
	
	/**
	 * Function checks whether path may be used as turbocache directory.
  	 *
  	 * @return bool TRUE when path is correct, otherwise FALSE
	 */
	private function _isPathPossible($path)
	{
		if ((preg_match('/^app[\/]{0,}(.)*/',          $path)) ||
		    (preg_match('/^404[\/]{0,}(.)*/',          $path)) ||
		    (preg_match('/^downloader[\/]{0,}(.)*/',   $path)) || 
		    (preg_match('/^js[\/]{0,}(.)*/',           $path)) ||
		    (preg_match('/^lib[\/]{0,}(.)*/',          $path)) || 
		    (preg_match('/^media[\/]{0,}(.)*/',        $path)) || 			
		    (preg_match('/^pkginfo[\/]{0,}(.)*/',      $path)) || 		
		    (preg_match('/^report[\/]{0,}(.)*/',       $path)) ||			
		    (preg_match('/^skin[\/]{0,}(.)*/',         $path)) ||				
		    (preg_match('/^var\/cache[\/]{0,}(.)*/',   $path)) ||		
		    (preg_match('/^var\/report[\/]{0,}(.)*/',  $path)) ||
		    (preg_match('/^var\/session[\/]{0,}(.)*/', $path)))	return FALSE;

		/* path is correct */		
		return TRUE;

	}

}
