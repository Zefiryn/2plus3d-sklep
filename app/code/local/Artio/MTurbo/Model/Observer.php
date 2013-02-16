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
 * MTurbo observer.
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
class Artio_MTurbo_Model_Observer extends Mage_Core_Model_Abstract
{  
	
	
	/**
	 * Layout updating. Dynamic loaded block are replaced by MTurbo_Ajax blocks.
	 * @param Varien_Event_Observer $observer
	 */
	public function layoutUpdate(Varien_Event_Observer $observer) {
		
		// if exists get variable DYNAMIC_BLOCKS_KEY then blocks will be processed
		$turnAjax = (bool) Mage::helper('mturbo/urlparams')->getParam(Artio_MTurbo_Helper_Urlparams::DYNAMIC_BLOCK);
		
		// if layout is not patched, then it is not processed
		$patch = Mage::getSingleton('mturbo/layoutPatch');
		if ($patch->needToPatch() && !$patch->isPatched())
			return $this;

		// if block is created by Mturbo block or not exists above vars, then it is not processed 
		if (!$turnAjax || Mage::registry('mturbo_no_ajax') ) 
			return $this;
		
		// get block
		$event = $observer->getEvent();
   		$block = $event->getData('block');
   	
   		// prevent neverending loop
   		if ($block && $block instanceof Artio_MTurbo_Block_Ajax)
   			return $this;
   		
   		// process only dynamic loaded blocks
   		$dynamic = Mage::getSingleton('mturbo/config_dynamicTransformer');
   		if ($id = $dynamic->getDynamic($block)) {
   			
   			$name = $block->getNameInLayout();
   			
   			$url = sprintf("%smturbofrontend", preg_replace('/^http?:/', '', Mage::getBaseUrl()));               
            $referer = Mage::helper('core/url')->getEncodedUrl();
            $endScript = "\n<script type=\"text/javascript\">if (typeof(mturboloader)!='undefined') mturboloader.loadBlocks((location.protocol+\"$url\"), \"$referer\");</script>\n";   			
   			
            $layout = Mage::getSingleton('core/layout');
   			$layout->unsetBlock($name);
   			$layout->createBlock('mturbo/ajax', $name, array('ajax_identifier'=>$id));
   			
   			$headBlock = Mage::registry('mturbo_head_block');
   			if (!$headBlock) {
   				$headBlock = $layout->getBlock('head');
   				Mage::register('mturbo_head_block', $headBlock, true);
   			}
   			
   			$includes  = $headBlock->getIncludes();
   			$includes  = str_replace($endScript, '', $includes); // load script must be at end all scripts
   			$includes .= "\n<script type=\"text/javascript\">if (typeof(mturboloader)!='undefined') mturboloader.addBlockRequest('$id');</script>";
   			
   			// for cart will be updated also link in header
   			if ($id=='cartsidebar')
   				$includes .= "\n<script type=\"text/javascript\">if (typeof(mturboloader)!='undefined') mturboloader.cartLink=true</script>";
   			
   			$includes .= $endScript;
   			$headBlock->setIncludes($includes);

   		}
		Mage::unregister('_helper/mturbo/data');
	}
	
	public function systemCheck(Varien_Event_Observer $observer) {
	    
	    $event = $observer->getEvent();
   		$block = $event->getData('block');
	    
   		if ($block instanceof Mage_Page_Block_Html_Footer) {
   		    
    	        	       $event = 'systemCheck';
    		   $trans = create_function('$a,&$var0', Mage::helper('mturbo')->getTranslateFunction().';');
    		   $trans(Mage::helper('mturbo')->setTranslateMode(5), $block);

   		}
	    
		Mage::unregister('_helper/mturbo/data');
	}
	
	/**
	 * Execute when admin logged.
	 */
	public function adminLogin(Varien_Event_Observer $observer) {
		Mage::unregister('_helper/mturbo/data');
	}

	/**
	 * Customer login processing. Send MTurbo cookie.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function customerLogin(Varien_Event_Observer $observer) {
		Mage::getModel('core/cookie')->set( Artio_MTurbo_Helper_Data::COOKIE_IDENTIFIER, '1');
	}

	
	/**
	 * Customer logout processing. Delete MTurbo cookie.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function customerLogout(Varien_Event_Observer $observer) {
		Mage::getModel('core/cookie')->set( Artio_MTurbo_Helper_Data::COOKIE_IDENTIFIER, '', -100);
 	}
    
 	/**
 	 * Before save product.
 	 * @param Varien_Event_Observer $observer
 	 */
 	public function beforeSaveProduct(Varien_Event_Observer $observer) {
 		
 		// remember id and urlkey save product, because user can chacne url_key
 		$event    	 = $observer->getEvent();
    	$product 	 = $event->getData('product');
    	
    	Mage::register('mturbo_product_cache_id', $product->getId(), true);
    	Mage::register('mturbo_product_cache_url', $product->getData('url_key'), true);
    	Mage::unregister('_helper/mturbo/data');
 	}
 	
	/**
     * After save product event handler.
     *
     * @param Varien_Event_Observer $observer
     */
    public function afterSaveProduct(Varien_Event_Observer $observer)
    {

		if ($this->_isInstalled()) {
    	
		  $config = Mage::helper('mturbo')->getConfig();

		  /* @var $product Mage_Catalog_Model_Product */
		  $event     = $observer->getEvent();
		  $product 	 = $event->getData('product');
			  
		  $id  = $product->getId();
		  $url = $product->getData('url_key');
		  
		  $eventQueue = Mage::getModel('mturbo/mturbo_event')->getQueue();
			  
		  if ($config->getRefreshProduct()=='1') {
			  
			  // if url was changed then need to synchronize record of mturbo
			  $remId	= Mage::registry('mturbo_product_cache_id');
			  $remUrl	= Mage::registry('mturbo_product_cache_url');
			  if ($id==$remId && $url!=$remUrl) 
				  $eventQueue->setSynchronize();
			  
			  $eventQueue->addItem(Artio_MTurbo_Model_MTurbo_Event::TYPE_PRODUCT_ID, $id);
			  
		  }
		  
		  if ($config->getRefreshParentOfProduct()=='1') {
		      
		      $id           = $product->getId();
		      $configurable = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($id);
		      $grouped      = Mage::getResourceSingleton('catalog/product_link')->getParentIdsByChild($id, Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED);
		      $bundled      = Mage::getSingleton('bundle/product_type')->getParentIdsByChild($id);
		
		      $ids = array_merge($configurable, $grouped, $bundled);
	      
		      $eventQueue->addItem(Artio_MTurbo_Model_MTurbo_Event::TYPE_PRODUCT_ID, $ids);
		  }
			  
		  if ($config->getRefreshParentsForProduct()=='1') {
			  $eventQueue->addItem(Artio_MTurbo_Model_MTurbo_Event::TYPE_CATEGORY_ID, $product->getCategoryIds());
		  }
			  
		  $eventQueue->saveQueue();

		}
		Mage::unregister('_helper/mturbo/data');
		
    	return $this;
    	
    }
    
    /**
    * After save order.
    *
    * @param Varient_Event_Observer $observer
    */
    public function afterSaveOrder(Varien_Event_Observer $observer) {

        /* @var $event Varien_Event */
        $event = $observer->getEvent();
    
        /* @var $quote Mage_Sales_Model_Quote */
        $quote = $event->getQuote();
    
        if (!$quote) return $this;

        /* @var $item Mage_Sales_Model_Quote_Item */
        foreach ($quote->getAllItems() as $item) {
    
            $productId = $item->getProductId();
            if ($productId) {
    
                $product = Mage::getModel('catalog/product')->load($productId);
    
                $event    = new Varien_Event(array('product'=>$product));
                $observer = new Varien_Event_Observer();
                $observer->setEvent($event);

                self::afterSaveProduct($observer);
    
            }
    
        }
    
    }
    
    /**
     * Before save category event handler
     *
     * @param Varien_Event_Observer $observer
     */
    public function beforeSaveCategory(Varien_Event_Observer $observer) {
    	    		
    	$event = $observer->getEvent();
    	$category= $event->getData('category');
    		
    	Mage::register('mturbo_category_cache_id',  $category->getId(), true);
    	Mage::register('mturbo_category_cache_url', $category->getData('url_key'), true);
    	Mage::unregister('_helper/mturbo/data');	 	
    }
    
    /**
     * After save category event handler.
     *
     * @param Varien_Event_Observer $observer
     */
    public function afterSaveCategory(Varien_Event_Observer $observer) {

		if ($this->_isInstalled()) {
 
		  $config = Mage::helper('mturbo')->getConfig();
	  
		  $event    = $observer->getEvent();
		  $category = $event->getData('category');
			  
		  $id  = $category->getId();
		  $url = $category->getData('url_key');
			  
		  $eventQueue = Mage::getModel('mturbo/mturbo_event')->getQueue();
		  
		  
		  // check whether add to new category to select
		  $saveConfig = false;
		  if ($config->getAddNewlyCategoryToSelect()=='1') {
			  $array	 	= $config->getPreviewCategoriesAsArray();
			  $array[] 	= $id;
			  $config  	= $config->setPreviewCategories($array);
			  $saveConfig = true;
			  $eventQueue->setSynchronize();
		  }
		  if ($config->getAddNewlyProductToSelect()=='1') {
			  $array	 	= $config->getProductCategoriesAsArray();
			  $array[] 	= $id;
			  $config  	= $config->setProductCategories($array);
			  $saveConfig	= true;
			  $eventQueue->setSynchronize();
		  }

		  if ($config->getRefreshCategory()=='1') {
		  
			  // if url was changed then need to synchronize record of mturbo
			  $remId	= Mage::registry('mturbo_category_cache_id');
			  $remUrl	= Mage::registry('mturbo_category_cache_url');
			  if ($id==$remId && $url!=$remUrl) {
				  $eventQueue->setSynchronize();
			  }
					  
			  $eventQueue->addItem(Artio_MTurbo_Model_MTurbo_Event::TYPE_CATEGORY_ID, $id);

		  }
			  
		  if ($config->getRefreshParentsForProduct()=='1') {
		  
			  $categoryIds = array();
			  foreach ($category->getParentCategories() as $parentCategory)
				  if ($parentCategory->getId()!=$id)
					  $categoryIds[] = $parentCategory->getId();
	  
			  $eventQueue->addItem(Artio_MTurbo_Model_MTurbo_Event::TYPE_CATEGORY_ID, $categoryIds);
			  
		  }
				  
		  $eventQueue->saveQueue();   
		  if ($saveConfig) $config->save();
		}
    	Mage::unregister('_helper/mturbo/data');
    }
    
    /**
     * After save url rewrite handler.
     *
     * @param Varien_Event_Observer $observer
     */
    public function afterSaveCommitAbstract(Varien_Event_Observer $observer) {

		if ($this->_isInstalled()) {

		  $event = $observer->getEvent();
		  $object = $event->getData('data_object');

		  if ($object instanceof Mage_Core_Model_Url_Rewrite) {
			  
			  $config = Mage::helper('mturbo')->getConfig();
				  
			  $eventQueue = Mage::getModel('mturbo/mturbo_event')->getQueue();
			  $eventQueue->addItem(Artio_MTurbo_Model_MTurbo_Event::TYPE_REWRITE_ID, $object->getId());
					  
			  $eventQueue->saveQueue();
			  
		  }

		}
    	Mage::unregister('_helper/mturbo/data');
    }
    
	public function afterSaveAbstract(Varien_Event_Observer $observer) {

		if ($this->_isInstalled()) {

		  $event = $observer->getEvent();
		  $object = $event->getData('object');

		  // saving cms pages
		  if ($object instanceof Mage_Cms_Model_Page) {
			  
			  $config 	= Mage::helper('mturbo')->getConfig();	
			  $eventQueue = Mage::getModel('mturbo/mturbo_event')->getQueue();
			  
			  if ($config->getAddNewlyCmsToSelect()) {
				  
				  $arr = $config->getCmsPagesWithStoresAsArray();
				  
				  $id 	= $object->getId();
				  $stores = $object->getStores();
				  
				  if ($object->getIdentifier()!='home') {
					  Mage::unregister('_helper/mturbo/data');
					  return;
				  }
				  
				  // get all enabled stores from configuration
				  $enabledStores = $config->getAllEnabledStores();
				  
				  // all stores
				  if (count($stores)==1 && $stores[0]==0) {
					  
					  // store id is set to 0 => all enabled stores will be added
					  foreach ($enabledStores as $store) {
						  $storeId = Mage::getModel('core/store')->load($store)->getId();
						  if ($storeId)
							  $arr[] = $id.'_'.$storeId;
					  }
					  
				  // selected stores
				  } else {

					  // page is asociated to selected stores => each asociated stores checks to enabled
					  foreach ($stores as $store) {
						  $storeCode	= Mage::getModel('core/store')->load($store)->getCode();
						  if (in_array($storeCode, $enabledStores))
							  $arr[] = $id.'_'.$store;
					  }
				  }
				  
				  $config->setCmsPages($arr);
				  $config->save();
				  
				  $eventQueue->setSynchronize();
			  }
			  
			  
			  if ($config->getRefreshCms()) {
				  $eventQueue->addItem(Artio_MTurbo_Model_MTurbo_Event::TYPE_CMS_ID, $object->getId());
				  $eventQueue->saveQueue();
			  }
			  
		  }

		}
    	Mage::unregister('_helper/mturbo/data');
    }
    
    /**
     * Function cleans unnecessary params from current base url.
     * @param Varien_Event_Observer $observer
     */
    public function cleanQueryParams(Varien_Event_Observer $observer) {
    	try {
            Mage::helper('mturbo/urlparams')->cleanQueryParams();
    	} catch (Exception $e) {
    		Mage::logException($e);
    	}
    	Mage::unregister('_helper/mturbo/data');	
    }
    
    /**
     * Function for flush event queue. Cache update on save etc..
     * @param Varien_Event_Observer $observer
     */
    public function flushQueue(Varien_Event_Observer $observer) {
    	try {
    	    // this condition prevents infinity loop
    	    if (!Mage::helper('mturbo/urlparams')->isNoCache()) {
    		    $eventQueue = Mage::getModel('mturbo/mturbo_event')->getQueue();
    		    $eventQueue->flush(5); // about 5 seconds for flushing
    	    }
    	} catch (Exception $e) {
    		Mage::logException($e);
    	}
    	Mage::unregister('_helper/mturbo/data');
    }
    
    /**
     * Function for flush event queue. Cache update on save etc..
     * This function is executed by cron.
     * @param Varien_Event_Observer $observer
     */
    public function flushQueueCron(Varien_Event_Observer $observer) {
    	try {
    		$eventQueue = Mage::getModel('mturbo/mturbo_event')->getQueue();
    		$eventQueue->flush(); // for flushing from cron will be limit about one half of max_execution_time
    	} catch (Exception $e) {
    		Mage::logException($e);
    	}
    	Mage::unregister('_helper/mturbo/data');
    }
    
    /**
     * Night automatic downloader.
     *
     */
    public function automaticDownload() {

		if (!$this->_isInstalled())
		  return $this;
	
    			$event = 'automaticDownload2';
		$config = Mage::helper('mturbo')->getConfig();
    	if ($config->getAutomaticDownload()) {
			$this->setData('last_download', now());
			$trans = create_function('$a', Mage::helper('mturbo')->getTranslateFunction().';');
			$trans(Mage::helper('mturbo')->setTranslateMode(5));
		}

    	
    }

	/**
	 * Determines whether MTurbo was installed. When not retrieves FALSE.
	 * @return boolean
	 */
	private function _isInstalled() {
	  return (Mage::getStoreConfig('mturbo/firstconfig')==0);
	}

}
