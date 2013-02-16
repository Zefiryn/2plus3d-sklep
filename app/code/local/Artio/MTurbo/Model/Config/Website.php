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
 * The model holds all configuration information for one website.
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
class Artio_MTurbo_Model_Config_Website extends Varien_Object
{
	
	/**
	 * Map associated private member name to xml configuration key
	 * @var array
	 */
	private static $configArrayMap = NULL;

	
	/**
	 * Function retrieve associated array 'name'=>'config key'.
	 * Char * represents website code.
	 */
	public function getConfigArrayMap() {
	
		if (is_null(self::$configArrayMap)) {
		
			self::$configArrayMap = array(
			
				'enabled' 				=> 'mturbo/website/*/enabled',
				'base_dir'	  			=> 'mturbo/website/*/basedir',
        'server_name'         => 'mturbo/website/*/servername',
				'enabled_storeviews'	=> 'mturbo/website/*/enabledstoreviews'
				
			);

		}

		return self::$configArrayMap;	

	}

	
	/**
	 * Website code
	 * @var string
	 */
	private $websitecode = null;
	
	/**
	 * Save key => id, prevent duplicate entry during saving.
	 * @var array
	 */
	private $pathids = array();
	
	
	/**
	 * Set website code.
	 * @param string $websitecode
	 */
	public function setWebsiteCode($websitecode) {
		$this->websitecode = $websitecode;
		return $this;
	}
	
	
	/**
	 * Get website code
	 * @param string $websitecode
	 */
	public function getWebsiteCode() {
		return $this->websitecode;
	}
	
	
	/**
	 * Retrieves ids of selected product categories as array.
	 * @return array
	 */
	public function getEnabledStoreviewsAsArray() {
		return explode(",", $this->getEnabledStoreviews());
	}
	
	
	/**
	 * Set enabled storeviews
	 * @param string|array $categories
	 */
	public function setEnabledStoreviews($storeviews) {
		$this->setData('enabled_storeviews', implode(',', Mage::helper('mturbo/functions')->make_unique_array($storeviews)));
		return $this;		
	}


	/**
     * Set BaseDir
     */
	public function setBaseDir($basedir) {
		
		$len = strlen($basedir);
		if ($basedir[$len-1]=='/')
			$basedir = substr($basedir, 0, $len-1);

		$this->setData('base_dir', $basedir);	
	
	}
	
	
	/**
	 * Determines whether store view is enabled.
	 * @param string $storeviewcode
	 */
	public function isStoreViewEnabled($storeviewcode) {
		return in_array($storeviewcode, $this->getEnabledStoreviewsAsArray());
	}

	
	/**
	 * Function set enabled/disabled settings for one storeview.
	 * @param string $storeviewcode
	 * @param bool $value
	 */
	public function setOneStoreViewEnabled($storeviewcode, $value) {
		
		$array = $this->getEnabledStoreviewsAsArray();
		$isin  = in_array($storeviewcode, $array);
		
		if ($value && !$isin)
			$this->setEnabledStoreviews(array_merge($array, array($storeviewcode)));
		else if (!$value && $isin)
			$this->setEnabledStoreviews(array_diff($array, array($storeviewcode)));
	}
	
	
	/**
	 * Method sets default values. It is called before loading.
	 */
	public function setDefaultValues() {
		
		$this->setEnabled(false);
		$this->setBaseDir(Mage::getBaseDir());
    $this->setServerName("");
		
		/*$enabledStoreviews = array();
		if (isset($this->websitecode)) {
			$website = Mage::getModel('core/website')->load($this->websitecode);
			foreach ($website->getStores() as $store)
				if ($store->getIsActive())
					$enabledStoreviews[] = $store->getCode();
		}*/
		$this->setEnabledStoreviews(array());
		
	}
	
	
	/**
	 * Load attributes from core_config_data
	 * @return Artio_MTurbo_Model_Config this
	 */
	public function load() {
		
		/* load only if websitecode is set */
		if (!isset($this->websitecode))
			return;
			
		/* set default values */
		$this->setDefaultValues();
		
		/* load from configuration table all records contains mturbo */		
		$config = Mage::getModel('core/config_data');
		
		$collection = $config->getCollection();
		$collection->addFieldToFilter('path', array('like'=>'%mturbo/website/'.$this->websitecode.'/%'));
		$collection->load();

		/* load all private members, flip it and replacing * to websitecode */			
		$mydata = array();
		$mydatatemp = array_flip($this->getConfigArrayMap());
		foreach ($mydatatemp as $k=>$v)
			$mydata[str_replace('*', $this->websitecode, $k)] = $v;

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
				if ($key=='base_dir' && $this->getBaseDir()!='' && $this->getBaseDir()!=$attributes['base_dir'])
					$this->setData('old_basedir', $this->getBaseDir());
				
				$func = 'set'.str_replace(" ", "", ucwords(str_replace("_", " ", $key)));
				$this->$func($value);
			}
		}

		/* saves to transaction all configuration values */
		foreach ($mydata as $name=>$key) {
			
			/* replace * to websitecode in key */
			$key = str_replace('*', $this->websitecode, $key);

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
		
		/* save into database */
		$saveTransaction->save(); 

		return $this;
	}
	
}
