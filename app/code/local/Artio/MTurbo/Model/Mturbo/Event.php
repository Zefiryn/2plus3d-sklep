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
 * MTurbo event model. Maintain requests for manipulation with cached entities.
 * (ex. product edit, insert category ...)
 *
 * Format event queue in configuration
 * EVENT!EVENT!EVENT!EVENT!
 * 
 * Format event
 * TYPE;ACTION;DATA
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
class Artio_MTurbo_Model_MTurbo_Event extends Mage_Core_Model_Abstract {
	
	const CONFIG_XML_PATH_MTURBO_EVENT = 'mturbo/event/queue';
	
	const TYPE_CATEGORY_ID 	= '1';
	const TYPE_PRODUCT_ID  	= '2';
	const TYPE_REWRITE_ID  	= '3';
	const TYPE_REQUEST	   	= '4';
	const TYPE_CMS_ID	   	= '5';
		
	private $type	= null;
	private $data	= null;
	
	/**
	 * @var bool
	 */
	public static $sync	 = null;
	
	/**
	 * @var array
	 */
	public static $instance = null;
	
	/**
	 * @var Artio_MTurbo_Model_MTurbo
	 */
	public static $model    = null;
	
	
	/**
	 * Get queue instance as singleton.
	 */
	public function getQueue() {
		
		if (!isset(self::$instance)) {
			
		    self::$model    = Mage::getModel('mturbo/mturbo');
			self::$instance = array();	
			
			$config = $this->_getQueueContents();
			
			if (!isset($config)) {
				self::$instance = null;
			} else {		
				self::$instance = $this->_restoreQueue($config);
			} 
	
		}
			
		return $this;
		
	}
	
	
	/**
	 * Special function for load contents of queue.
	 * Using function Mage::getStoreConfig is not possible, because it use cache.
	 */
	private function _getQueueContents() {
		
		$config = Mage::getModel('core/config_data');
		$collection = $config->getCollection();
		$collection->addFieldToFilter('path', array('like'=>self::CONFIG_XML_PATH_MTURBO_EVENT));
		$collection->load();
		
		$items 	= $collection->getItems();
		$item   = array_shift($items);
		
		return (isset($item)) ? $item->getData('value') : null;
		
	}
	
	
	/**
	 * Function restores queue from configuration.
	 */
	private function _restoreQueue($string) {

		/* splits into events */
		$configArray = explode("!", $string);
		
		if (!is_array($configArray)) 
			return null;
		
		$sync = array_shift($configArray);
		if ($sync=='1')
			self::$sync = true;
		
		$result = array();
		
		/* foreach events */
		foreach ($configArray as $configItem) {
				
			$configItemArray = explode(";", $configItem);
			
			/* if config event is corect then add event to queue */
			$i = 0;
			$c = count($configItemArray);
			while ($i<$c-1) {
				
				$event = Mage::getModel('mturbo/mturbo_event');
				$event->setType($configItemArray[$i++]);
				
				$data = $configItemArray[$i++];
				if (strpos($data, ',')>0)
					$event->setData('ids', explode(",", $data));
				else 
					$event->setData('ids', $data);
				
				$result[] = $event;
				
			}
		}

		/* if queue is empty return null */
		return count($result)>0 ? $result : null;
		
	}
	
	
	/**
	 * Function save queue in to configuration.
	 */
	public function saveQueue() {
		
		try {
				
			$config = Mage::getModel('core/config_data');
			
			/* load config record */
			$collection = $config->getCollection();
			$collection->addFieldToFilter('path', array('like'=>self::CONFIG_XML_PATH_MTURBO_EVENT));
			$collection->load();
			
			/* build config record, if it is empty set path */
			$dataObject = null;
			if ($collection->getSize()==0) {
				$dataObject = Mage::getModel('core/config_data');
				$dataObject->setPath(self::CONFIG_XML_PATH_MTURBO_EVENT);
			} else {
				foreach ($collection as $item) {
					$dataObject = $item;
					break;
				}
			}
			
			$dataObject->setValue($this->_toSerializeString());

			$dataObject->save();
			
		} catch (Exception $e) {
			Mage::log("MTurbo: Saving event queue failed. " . $e->getMessage());
			Mage::logException($e);
		}
		
	}
	
	
	/** 
	 * Serialized event to string.
	 */
	private function _toSerializeString() {
				
		$result = (self::$sync) ? '1!' : '0!';	
		
		if (isset(self::$instance)) {
			
			foreach (self::$instance as $event) {
				$result .= $event->getData('type').';';
				$result .= is_array($event->getData('ids')) ? implode(",", $event->getData('ids')).';' : $event->getData('ids').';';
			}
			$result .= '!';
		}
		
		return $result;
		
	}
	
	/**
	 * Set syncronize indicator. 
	 * Before flush will be mturbo synchronized.
	 */
	public function setSynchronize() {
		self::$sync = true;
	}
	
		
	/**
	 * Add item into queue
	 * @param int $type constant TYPE_*
	 * @param int $action constant ACTION_*
	 * @param string $data added data
	 */
	public function addItem($type, $data) {
		
		if (!isset(self::$instance)) {
			self::$instance = array();
		}

		$event = Mage::getModel('mturbo/mturbo_event');
		$event->setData('type', $type);
		$event->setData('ids', $data);

		self::$instance[] = $event;
			
	}
	
	/**
	 * Retrieve default time limit.
	 */
	public function getDefaultLimit()
	{
	    return 90;
	}
	
	/**
	 * Flush current queue.
	 */
	public function flush($limit = null) {
		    
	    if (!isset(self::$instance))
	        return; 
	    
	    if (!$limit)
	        $limit = $this->getDefaultLimit();
        
	    $time = $this->flushSynchronize();
	    
	    if ($time>1)
	        Mage::log("MTurbo <synctime>: $time s");
	    
	    if ($time>$limit) {
	        $this->saveQueue();
	        self::$instance = null;
	        self::$sync = null;
	        return;
	    }
	    
	    $processedKeys = array();
	    
		foreach (self::$instance as $key=>$action) {		
		    $time += $this->flushAction($action);		

		    $processedKeys[] = $key;
		    
		    if ($time>$limit)
	           break;	
		}
		
		foreach ($processedKeys as $k)
		    unset(self::$instance[$k]);
		
		$this->saveQueue();   
		self::$instance = null;
	    self::$sync = null; 
	}
	
	/**
	 * Flush synchronize bit and retrieve time for executing.
	 * @return float
	 */
	public function flushSynchronize()
	{
	    $start = microtime(true);

		if (self::$sync) {
			self::$model->synchronize();
			self::$sync = null;
		}
		
		return microtime(true)-$start;
	}
	
	/**
	 * Flush items in the request and retrieve time for executing
	 */
	public function flushAction($action)
	{
	    $start = microtime(true);
	    	    
	    $collection = null;
		switch ($action->getData('type')) {
			
			case self::TYPE_CATEGORY_ID:
				$collection = self::$model->getCollectionByCategoryIds($action->getData('ids'));
				break;
			case self::TYPE_PRODUCT_ID:
				$collection = self::$model->getCollectionByProductIds($action->getData('ids'));
				break;
			case self::TYPE_REQUEST:
				break;
			case self::TYPE_CMS_ID:
				$collection = self::$model->getCollectionByCmsIds($action->getData('ids'));
				break;
			case self::TYPE_REWRITE_ID:
				break;
			
		}

		if (isset($collection)) {
			foreach ($collection->getItems() as $item) {
				if (!$item->isBlocked()) 
					$item->getFileModel()->downloadPage();
			}
		}
	    
	    return microtime(true)-$start;
	}
	  
}