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
 * MTurbo file model. Maintain page in the disk.
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
class Artio_MTurbo_Model_MTurbo_File extends Mage_Core_Model_Abstract {

	/* used extension for cached pages */
	const EXT 		= '.html';
	/* used identifier for frontpage */
	const FRONTPAGE = 'home';
	
	/**
	 * Parent MTurbo model
	 * @var Artio_MTurbo_Model_MTurbo
	 */
	private $mturbomodel;

	
 	/**
	 * Set parent MTurbo model.
	 *
	 * @param Artio_MTurbo_Model_MTurbo $mturbomodel
	 */
	public function setTurboModel($mturbomodel) {
		$this->mturbomodel = $mturbomodel;
	}
	

	/**
	 * Get parent MTurbo model.
	 *
	 * @return Artio_MTurbo_Model_MTurbo
	 */
	public function getTurboModel() {
		return $this->mturbomodel;
	}

	
	/**
	 * Delete cached page.
	 */
	public function deletePage() {
		if (is_writeable($this->getAbsolutePath())) {
		  return unlink($this->getAbsolutePath());
		} else {
		  return true;
		}
	}
	

	/**
	 * Retrieve change time. 
	 */
	public function getChangeTime() {
		$unix = filectime( $this->getAbsolutePath() );
		return date('Y-m-d H:i:s', $unix);
	}

	
	/**
	 * Determines whether exist cached page.
	 * @return bool
	 */
	public function existPage() {
		return file_exists( $this->getAbsolutePath() );
	}
	
	
	/**
	 * Retrives size where really occupied in the harddisk.
	 */
 	public function getPageSize() {
    	return filesize( $this->getAbsolutePath() );	
    }

	    
  	/**
     * Retrieve download url with no cache query string
     *
     * @return string download url witt no cache query string
     */
    public function getDownloadUrlWithNoCache() {
    	
    	$originalUrl = $this->getDownloadUrl();
    	
    	/* 
    	 * if url contains query string  
    	 * then add nocache as next parameter
    	 */
    	if (strpos($originalUrl, '?')>0) {
    		return $originalUrl.'&nocache=true&mturbo_dynamic_block=true';
    	} else {
    		return $originalUrl.'?nocache=true&mturbo_dynamic_block=true';
    	}

    }
    
    
    /**
     * Retrieve download url
     * @return string download original url
     */
    public function getDownloadUrl() {
    	
    	/* get request path */    	
  		$requestPath = $this->mturbomodel->getRequestPath();
   
		/* base url */
  		$baseUrl = Mage::getStoreConfig('web/unsecure/base_url', $this->mturbomodel->getStoreId());
		$baseUrl = str_ireplace('/index.php/admin', '', $baseUrl);
   		$baseUrl = str_ireplace('/index.php', '', $baseUrl);

		/* get store code */
   		$storeCode = $this->mturbomodel->getStoreCode();
   		
		/* url is base url */
   		$url = $baseUrl;
   		
   		/* add store_code, when necessary */
		if (Mage::getStoreConfig('web/url/use_store')=='1') {
			$baseUrl .= $storeCode.'/';
		}
		
		/* add request_path, when necessary */
		if ($requestPath != self::FRONTPAGE) {
			$baseUrl .= $requestPath;
		}
		
		/* if store_code is not in url then add as query string */
		if (Mage::getStoreConfig('web/url/use_store')=='0') {
			$baseUrl .= '?___store='.$storeCode;
		}
			
		return $baseUrl;
				
    }

	
	/**
	 * Retrieve complete path to cached file.
	 * @return string
	 */
	public function getAbsolutePath() {
		$config = Mage::getSingleton('mturbo/config');
		$websiteconfig = $config->getWebsiteConfigByStoreviewCode($this->getTurboModel()->getStoreCode());
		return $websiteconfig->getBaseDir().DS.$config->getTurbopath().DS.$this->getRelativePath();
	}

	
	/**
	 * Retrieves relative path from root web.
	 *
	 * @return string
	 */
	public function getRelativePath() {
		
		/*
		 * Path-to-file rules:
		 * 
		 *  url: www.foo.com/subpath1/subpath2/subpath3
		 * path: subpath1/subpath2/subpath3.html
		 * 
		 * Except are frontpages with request /home !
		 * 
		 *  url: www.foo.com/storecode/
		 * path: storecode.html
		 * 
		 *  url www.foo.com/
		 * path: DEFAULT_STORE_CODE.html
		 * 
		 * DEFAULT_STORE_CODE is store code of default storeview.
		 * 
		 * Url makes by unsecure base url, store code and request.
		 * Store code is used even when set not to use the store code in the url.
		 * 
		 */
		
		/* path is empty */
		$path = '';
		
		/* get information about model */
		$storeCode = $this->mturbomodel->getStoreCode();
		$baseUrl   = $this->mturbomodel->getBaseUrl();
		$request   = $this->mturbomodel->getRequestPath();
		
		/* trim request suffix */
		$requestArray = explode('.', $request);
		$request      = $requestArray[0];
		
		if ($request == self::FRONTPAGE) {
			
			/* if frontpage then path is storecode.html */
			$path = $storeCode;
			
		} else {
		
			/* make url without http:// or https:// */
			$completeUrl = $baseUrl.$storeCode.DS.$request;
			$completeUrl = str_replace('https://', '', $completeUrl);
			$completeUrl = str_replace('http://', '', $completeUrl);
		
			/* explode to array, array[0] is host */
			$urlArray = explode(DS, $completeUrl);
			
			/* remove host */
			array_shift($urlArray);
			
			/* make back to path */
			$path = implode(DS, $urlArray);
			
		}
		
		/* fix endslash */
		if (mb_substr($path, -1)==DS) {
		    $path = mb_substr($path, 0, mb_strlen($path)-1);
		}
		
		/* add extension */
		$path .= self::EXT;
		return $path;
		
	}


 	/**
	 * Download page and save as static html.
	 * @param $marked determines whether will be appended current timestamp
	 * @param $saved determines whether downloaded page will be saved in the harddisk
	 * @param $resultTest output of result test string
	 * @param $method used method (default method gather from configuration)
	 */
	public function downloadPage($marked=true, $saved=true, &$resultTest='', $method='') {

  			if ($this->getTurboModel()->getType()=='cms' && $this->getTurboModel()->getRequestPath()!='home')
  				return;
  			
    		$resultTest = '';
    		$html = '';

			/* get configuration model */
			$config = Mage::getSingleton('mturbo/config');
    	
			/* if method is empty se default method */	
    		if ($method=='')
    			$method = $config->getDownloadMethod();

    		/* creath path when necessary, get url */
    		if ($saved) {
    			$path	 = $this->getAbsolutePath();
    			$dirpath = dirname($path);
    			if (!file_exists($dirpath)) {
					if (!Mage::helper('mturbo/functions')->create_dirs($dirpath))
						Mage::throwException(Mage::helper('mturbo')->__("I can't create '%s'. Please, check permission to create this directory.", $dirpath));
				}
    			$url	= $this->getDownloadUrlWithNoCache();
    		} else {
    			$url	= Mage::getUrl();
				$url	= str_replace("admin/", "", $url);
    		}
    		
    		/* get download method */
    		$downloadMethodsFactory = Mage::getModel('mturbo/downloadMethodsFactory');
    		$downloadMethod = $downloadMethodsFactory->getMethod($method);
    		
    		try {
    			
    			$html = $downloadMethod->downloadPage($url);
    			if (!$this->_checkHtml($html)) {
                    Mage::throwException(Mage::helper('mturbo')->__('Page is too small or 404'));       
                }

    		} catch (Exception $e) {
                if ($saved) Mage::throwException($e->getMessage());
    			$resultTest = $downloadMethod->getErrorMessage() . '(' . $e->getMessage() . ')';
    			return;
    		}
    	
			/* marks file when it is turned */	
			if ($marked) $html.= "<!-- " . date('D M j H:i:s e o') . " -->";

			/* save file in the hard whet it is turned */    	
			if ($saved) file_put_contents($path, $html);

    		if ($resultTest=='' && is_string($html) && ($html!=''))
    			$resultTest = round((strlen($html) / (float)1024), 2);
    		else if ($resultTest=='' && $html=='')
    			$resultTest = Mage::helper('mturbo')->__('empty output');
    		
    		
	}

	/**
	 * Function checks download html. Retrieves FALSE when file is too little or
	 * find within 404 code
	 * @param $html checked html
	 * @return bool TRUE when html is correct, otherwise FALSE
     */
	private function _checkHtml(&$html) {

      if (!is_string($html))
        return false;

      if (strlen($html) < Mage::getSingleton('mturbo/config')->getMinimalPageSize())
        return false;

	  $title = (string) Mage::helper('mturbo')->getNoRouteTitle($this->mturbomodel->getStoreId());
      if (strlen($title)>1) { // if title is empty then quite ignoring
        if (strpos($html, "<title>$title")!==false) {
            return false;
        }
      }

      return true;

    }

      
	/**
	 * Clear all pages.
	 */
	public function clearAllPages() {

		$config 	= Mage::getSingleton('mturbo/config');
		$turbopath	= $config->getTurbopath();
		$pattern	= '/(.)*\.html/';
		
		$websites	= Mage::getModel('core/website')->getCollection()->load();
		foreach ($websites as $website) {
			
			$websiteConfig = $config->getWebsiteConfig($website->getCode());

			$baseDir = $websiteConfig->getBaseDir();
			
			Mage::helper('mturbo/functions')->unlink_recursive($baseDir.DS.$turbopath, $pattern);
				
			$htaccess = Mage::getModel('mturbo/htaccess')->setWebsiteCode($website->getCode());
			$htaccess->copySideHtaccess();

		}
		
	}
  
}
