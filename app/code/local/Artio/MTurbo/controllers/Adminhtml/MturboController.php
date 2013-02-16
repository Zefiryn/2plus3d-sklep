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
 * Mainly controller serves most user actions.
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
class Artio_MTurbo_Adminhtml_MturboController extends Mage_Adminhtml_Controller_Action
{

	/**
	 * Initailization mainly adminhtml xml layout.
	 */
	protected function _initAction() {
		
		$this->loadLayout();   
		$this->getLayout()
			->getBlock('head')
			->setCanLoadExtJs(true)
            ->setContainerCssClass('catalog-categories');
		
    	$this->_setActiveMenu('system');
    	$this->_addBreadcrumb(Mage::helper('adminhtml')->__('M-Turbo Management'), Mage::helper('adminhtml')->__('M-Turbo Management'));
    	
    	return $this;

	}

	
	/**
	 * Executed when user select MTurbo Management in the main menu. 
	 */
	public function indexAction() {		
		
		/* after first executes redirect to install pages, otherwise show index layout */
		$config = Artio_MTurbo_Helper_Data::getConfig();
		if ($config->getData('firstconfig')=='1') {
			$this->_redirect('mturbo/adminhtml_mturbo/first');
		} else {
			$this->_initAction()->renderLayout();
		}
		
	}
	

	/**
   	 * Executed when user firstly select MTurbo Management in the main menu.
	 */	
	public function firstAction() {
		$this->_initAction()->renderLayout();
	}
	
	
	/**
	 * Uninstall Magento
	 */
	public function uninstallAction() {

        $configModules = Mage::getBaseDir().DS.'app'.DS.'etc'.DS.'modules'.DS.'Artio_MTurbo.xml';
        if (!is_writeable($configModules)) {
          $this->_getSession()->addWarning(Mage::helper('mturbo')->__("File '%s' is not writeable, please change permission.", $configModules));
          $this->_redirect('mturbo/adminhtml_mturbo/index', array('activeTab'=>'page_tabs_uninstall_section'));
          return;
        }

        try {
            // remove mage patch
            $patch = Mage::getSingleton('mturbo/patch');
            if ($patch->isPatched()) {
                $patch->removePatch();
                $this->_getSession()->addSuccess(Mage::helper('mturbo')->__('Mage patch was removed.'));
            }
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('mturbo')->__('Mage patch uninstall error').' : '.$e->getMessage());
        }
            
        try {
            // remove layout patch
            $laypatch = Mage::getSingleton('mturbo/layoutPatch');
            if ($laypatch->isPatched()) {
                $laypatch->removePatch();
                $this->_getSession()->addSuccess(Mage::helper('mturbo')->__('Layout patch was removed.'));
            }
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('mturbo')->__('Layout patch uninstall error').' : '.$e->getMessage());
        }

        try {       
            // remove mturbo directives from htaccess
            $htacc = Mage::getModel('mturbo/htaccess');
            $htacc->actionAllWebsites('remove');
            $this->_getSession()->addSuccess(Mage::helper('mturbo')->__('M-Turbo directives from .htaccess was removed.'));
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('mturbo')->__('Removing M-Turbo directives from .htaccess error').' : '.$e->getMessage());
        }
            
        try {
            // clear directories
            $websites = Mage::getModel('core/website')->getCollection()->load()->getItems();
            $config   = Mage::getSingleton('mturbo/config');
            foreach ($websites as $website) {
                $configWeb = $config->getWebsiteConfig($website->getCode());
                if ($configWeb && $config->getTurbopath()!='') {
                    $dir = $configWeb->getBaseDir().DS.$config->getTurbopath();
                    Mage::helper('mturbo/functions')->unlink_recursive($dir, '/.*/', true);
                }
            }
            $this->_getSession()->addSuccess(Mage::helper('mturbo')->__('Turbocache directory was removed.'));
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('mturbo')->__('Clearing turbocache directory error').' : '.$e->getMessage());
        }
            
        try {
            // clear db records
            $prefix = Mage::app()->getConfig()->getTablePrefix();
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            $connection->query("
            
                DROP TABLE IF EXISTS `".$prefix."mturbo`;

                DELETE FROM `".$prefix."core_config_data` WHERE `path` LIKE 'mturbo/%' OR `path` LIKE 'crontab/jobs/mturbo%';
                DELETE FROM `".$prefix."core_resource` WHERE `code` LIKE 'mturbo_setup';
                DELETE FROM `".$prefix."adminnotification_inbox` WHERE `url` LIKE 'http://www.artio.net/magento-extensions/m-turbo-accelerator';
                
            ");
            $this->_getSession()->addSuccess(Mage::helper('mturbo')->__('Db records was removed.'));
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('mturbo')->__('Uninstall db records error').' : '.$e->getMessage());
        }

        try {
            exec('./pear uninstall connect.magentocommerce.com/community/MTurbo > var/uninstallmturbo.log');
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('mturbo')->__('Uninstall MTurbo from PEAR error').' : '.$e->getMessage());
        }
        
        try {   

            $uninstalledPear = true;
            $baseDir         = Mage::getBaseDir();
  
            $dirs = array(
              'app'.DS.'code'.DS.'local'.DS.'Artio'.DS.'MTurbo',
              'app'.DS.'design'.DS.'adminhtml'.DS.'default'.DS.'default'.DS.'template'.DS.'mturbo',
              'app'.DS.'etc'.DS.'modules'.DS.'Artio_MTurbo.xml',
              'app'.DS.'design'.DS.'adminhtml'.DS.'default'.DS.'default'.DS.'layout'.DS.'mturbo.xml',
              'app'.DS.'design'.DS.'frontend'.DS.'default'.DS.'default'.DS.'layout'.DS.'mturbo.xml',
              'app'.DS.'locale'.DS.'en_US'.DS.'Artio_MTurbo.csv',
              'skin'.DS.'frontend'.DS.'default'.DS.'default'.DS.'js'.DS.'mturbo.js'
              );

            $result = file_get_contents($baseDir.DS.'var'.DS.'uninstallmturbo.log');
            $array  = array();

            if (strpos($result, 'uninstall failed')!==false) {
                $this->_getSession()->addWarning(Mage::helper('mturbo')->__('Uninstall PEAR package failed. Probably you have not permission to remove files. Please, go to System/Magento Connect Manager and there finish uinstall of MTurbo. More information about uninstall of MTurbo you can get in file var/uninstallmturbo.log. '));              
            } else if (strpos($result, 'magento-community/MTurbo not installed')!==false) {
              
              foreach ($dirs as $dir) {
                if (!Mage::helper('mturbo/functions')->unlink_recursive($baseDir.DS.$dir, '/.*/', true)) {
                  $array[] = $dir;
                }
              }

            }

            if (count($array)>0) {
              $this->_getSession()->addWarning(Mage::helper('mturbo')->__('Some files were not deleted:<br />').implode('<br />', $array));
            }
                    
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('mturbo')->__('Uninstall error').' : '.$e->getMessage());
        }


        $this->_getSession()->addSuccess(Mage::helper('mturbo')->__('Uninstall complete. Please refresh standard Magento Cache'));
        $this->_redirect('adminhtml/dashboard');
        
	}
	

  /**
   * Executed when user push button install on the install page.
   */
	public function installAction() {
		
		/* get data from request */
		$request = $this->getRequest();
		
		try {

			/* extract post data for websites configuration */
			$config = Mage::getSingleton('mturbo/config');
			Mage::getSingleton('mturbo/config_websiteTransformer')->extractData($config, $request->getPost());
			$config->setFirstconfig('0');
			$config->save($request->getPost());
			
			$htacc = Mage::getSingleton('mturbo/htaccess');
      try {
        $htacc->actionAllWebsites('rebuild');
      } catch (Exception $e) {
				$this->_getSession()->addWarning(Mage::helper('mturbo')->__("Some .htaccess was not builded. Please check it on 'Website Configuration'").' : '.$e->getMessage());
			}

			$laypatch = Mage::getModel('mturbo/layoutPatch');
			try {
				if ($laypatch->needToPatch()) {
					$laypatch->applyPatch();
					if (!$laypatch->isPatched())
						$this->_getSession()->addWarning(Mage::helper('mturbo')->__("'Mage_Core_Model_Layout' was not patched. It is required for dynamic loaded blocks. Please see to 'Dynamic loaded blocks'."));
				}
			} catch (Exception $e) {
				$this->_getSession()->addWarning(Mage::helper('mturbo')->__("'Mage_Core_Model_Layout' was not patched. It is required for dynamic loaded blocks. Please see to 'Dynamic loaded blocks'. Exception %s", $e->getMessage()));
			}
			
			$this->_getSession()->addSuccess(Mage::helper('mturbo')->__('Installation complete. Welcome!!!'));
			
		} catch (Exception  $e) {
			$this->_getSession()->addError(Mage::helper('mturbo')->__('Install error').' : '.$e->getMessage());
		}
		
		$this->_redirect('mturbo/adminhtml_mturbo/index');
		
	}


	/**
   	 * Executed when user push button "Cache all pages" on the Action Tab or push button "Cache selected pages"
   	 * in the mainly grid. Response will be redirect to extra window in user browser and executes AJAX for
	 * downloading pages.
   	 */
	public function downloadAction() {
    	
    	if (!$this->_checkLicence()) return;
    	
    		$importids = $this->getRequest()->getParam('massrefresh');

			/* user push button "Cache all pages" */    	
			if (empty($importids)) {
	
			$html  = $this->getLayout()->createBlock('mturbo/adminhtml_run')->toHtml();
    		
		/* user push button "Cache selected pages" */    	
    	} else {

			/* for selected pages must be send importids into run block */ 
    		$importidsArray = explode(",", $importids);
    		if (is_array($importidsArray)) {
    			$html = $this->getLayout()->createBlock('mturbo/adminhtml_run')->setImportIds($importidsArray)->toHtml();
    		}

    	}
    	
		$this->getResponse()->setBody($html);
   		
	}

	
	/**
   	 * Executed when user push button "Generate URL list file" on the actions tab.  
   	 */ 
	public function generateurllistAction() {
		
  		if (!$this->_checkLicence()) return;

   		try {
   			
   			/* getting website code from request */
			$websitecode = $this->getRequest()->getParam('websitecode', '');
		
			/* if website code is empty, rebuild all htaccesses */
			$websiteCodes = array();
			if ($websitecode=='') {
				$websites = Mage::getModel('core/website')->getCollection()->load();
			} else {
				$websites   = array();
				$websites[] = Mage::getModel('core/website')->load($websitecode);
			}
		
			/* rebuild htaccess of all codes in $websiteCodes */
			foreach ($websites as $website) {
				try {
					$count = Mage::getModel('mturbo/mturbo')->generateUrlList($website);
					$this->_getSession()->addSuccess(Mage::helper('mturbo')->__("Generating complete for website '%s'. Wrote %d urls", $website->getName(), $count));
				} catch (Exception $e) {
					$this->_getSession()->addError(Mage::helper('mturbo')->__("Generating for website '%s' fail.", $website->getName()).' '.$e->getMessage());
				}
			}

		} catch (Exception  $e) {
			$this->_getSession()->addError(Mage::helper('mturbo')->__('Generate error').' : '.$e->getMessage());
		}

		/* redirect to tab */
		if ($websitecode!='')
			$this->_redirect('mturbo/adminhtml_mturbo/index', array('activeTab'=>'page_tabs_website_section'));
		else
			$this->_redirect('mturbo/adminhtml_mturbo/index', array('activeTab'=>'page_tabs_actions_section'));
    	
	}
    

	/**
   	 * Executed when user push button "Clear all pages" on the actions tab.
   	 */
 	public function clearpagesAction() {
    	
  		if (!$this->_checkLicence()) return;
    	
   		try {

   			Mage::getModel('mturbo/mturbo')->getFileModel()->clearAllPages();
    		$this->_getSession()->addSuccess( Mage::helper('mturbo')->__('All pages was succesfully removed') );

   		} catch (Exception $e) {
    		$this->_getSession()->addError( Mage::helper('mturbo')->__('Remove error:') . $e->getMessage()  );
   	 	}	

		/* redirect to action tab */   
    	$this->_redirect('mturbo/adminhtml_mturbo/index', array('activeTab'=>'page_tabs_actions_section'));
    	
   }


	/**
   	 * Executed when user push button "Synchronize Rewrite Table". 
   	 */
	public function synchronizeAction() {
    	
  		if (!$this->_checkLicence()) return;

   		try {

			$model = Mage::getModel('mturbo/mturbo');
			$model->synchronize();

			$this->_getSession()->addSuccess(Mage::helper('mturbo')->__('Synchronization complete'));

		} catch (Exception  $e) {
			$this->_getSession()->addError(Mage::helper('mturbo')->__('Synchronization error').' : '.$e->getMessage());
		}
    	
		/* redirect to action tab */
    	$this->_redirect('mturbo/adminhtml_mturbo/index',  array('activeTab'=>'page_tabs_url_section'));
    	
	}

	
	/**
	 * Executed when user pushes button "Rebuild Htaccess".
	 */
	public function htaccessbuildAction() {
		
		/* getting website code from request */
		$websitecode = $this->getRequest()->getParam('websitecode', '');
		
		/* if website code is empty, rebuild all htaccesses */
		$websiteCodes = array();
		if ($websitecode=='') {
			$websites = Mage::getModel('core/website')->getCollection()->load();
			foreach ($websites->getItems() as $website)
				$websiteCodes[$website->getCode()] = $website->getName();
		} else {
			$websiteCodes[$websitecode] = Mage::getModel('core/website')->load($websitecode)->getName();
		}
		
		/* rebuild htaccess of all codes in $websiteCodes */
		foreach ($websiteCodes as $code=>$name) {
			try {
				Mage::getModel('mturbo/htaccess')->setWebsiteCode($code)->rebuildHtaccess();
				$this->_getSession()->addSuccess(Mage::helper('mturbo')->__("Htaccess for website '%s' was rebuilded.", $name));
			} catch (Exception $e) {
				$this->_getSession()->addError(Mage::helper('mturbo')->__("Rebuild htaccess for website '%s' fail.", $name).' '.$e->getMessage());
			}
		}
		
		/* redirect to tab */
		if ($websitecode!='')
			$this->_redirect('mturbo/adminhtml_mturbo/index', array('activeTab'=>'page_tabs_website_section'));
		else
			$this->_redirect('mturbo/adminhtml_mturbo/index', array('activeTab'=>'page_tabs_actions_section'));
		
	}


	/**
	 * Executed when user changes download method on the configuration tabs.
	 * Action is called by AJAX.
	 */
	public function testdownloadAction() {
		
		/* getting method code and build method */
		$code 	= $this->getRequest()->getPost('method', '');	
		$method = Mage::getSingleton('mturbo/downloadMethodsFactory')->getMethod($code);

		/* download page and get its size */
		$size = strlen($method->downloadPage(Mage::getBaseUrl()));
		Mage::log($method->getErrorMessage());
		/* make result by download size */
		$resultTest = ($size > 0) ? round($size/(float)1024, 2) : $method->getErrorMessage();
		$res = ($size > 0);
		
		$result = array(
			'ok'	 		=> $res,
			'resultTest'	=> $resultTest
		);
		
		/* send result to client */
		$this->getResponse()->setBody(Zend_Json::encode($result));

	}

	
	/**
	 * Executed when user pushs "Apply patch" on the config tabs.
	 */
	public function magepatchAction() {
			
		try {

			$patch = Mage::getModel('mturbo/patch');
			if ($patch->isPatched()) {
				$patch->removePatch();
				$this->_getSession()->addSuccess(Mage::helper('mturbo')->__('Patch was removed.'));
			} else { 
				$patch->applyPatch();
				$this->_getSession()->addSuccess(Mage::helper('mturbo')->__('Patch was applied.'));
			}

		} catch (Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		}	
		
		$this->_redirect('mturbo/adminhtml_mturbo/index', array('activeTab'=>'page_tabs_main_section'));
		
	}
	
	/**
	 * Executed when user pushs "Apply patch" on the config tabs.
	 */
	public function layoutpatchAction() {
			
		try {

			$patch = Mage::getModel('mturbo/layoutPatch');
			if ($patch->isPatched()) {
				$patch->removePatch();
				$this->_getSession()->addSuccess(Mage::helper('mturbo')->__('Patch was removed.'));
			} else { 
				$patch->applyPatch();
				$this->_getSession()->addSuccess(Mage::helper('mturbo')->__('Patch was applied.'));
			}

		} catch (Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		}	
		
		$this->_redirect('mturbo/adminhtml_mturbo/index', array('activeTab'=>'page_tabs_dynamic_section'));
		
	}
	


	/**
   	 * Executed when user pushs button "Upgrade to Full version" at right top corner.
   	 **/
	public function upgradeAction() {
		
	    try {
	    
      		$message = Mage::helper('mturbo/downloader')->downloadAndUpgrade();
        	if ($message=='') {
        		$this->_getSession()->addSuccess(Mage::helper('mturbo')->__('Upgrade complete, please refresh Magento system cache'));
       		} else {
        		$this->_getSession()->addWarning($message);
       	 	}
       	 	
	    } catch (Exception $e) {
	        $this->_getSession()->addError($e->getMessage());
	        Mage::logException($e);
	    }
    	
    	$this->_redirect('*/*/index', array('activeTab'=>'page_tabs_license_section'));
 	}



	/* GRID ACTIONS ================================================================================== */


	/**
   	 * Executed when user clicks on the link "block" in the row with any page.
   	 */
	public function blockAction() {
  		$this->_stateAction(1);
 	}
 

	/**
   	 * Executed when user clicks on the link "unblock" in the row with any page.
   	 */  
 	public function unblockAction() {
   		$this->_stateAction(0);
 	}
    

	/**
	 * Executed when user changes the state for any one page.
	 */  
 	private function _stateAction($state) {
    	
  		$id = $this->getRequest()->getParam('id');
    	
  		try {

			/* during saving model decides to page deleted or not */
    		$mturbo = Mage::getModel('mturbo/mturbo')
      			->load($id)
       			->setBlocked($state)
       			->setIsMassupdate(true)
       			->save();

     		$this->_getSession()->addSuccess($this->__('Record was successfully updated.'));
                	
   		} catch (Exception $e) {
    		$this->_getSession()->addError($e->getMessage());
   		}
        
   		$this->_redirect('*/*/index');
        
 	}
	
	/**
	 * Executed when user selects some pages and in the massaction selects delete.
	 */	
	public function massDeleteAction() {
    	
		$ids = $this->getRequest()->getParam('mturbo');
    	
  		if(!is_array($ids)) {
			$this->_getSession()->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
   		} else {
        	
     		try {
            	
      			foreach ($ids as $id) {
        			$mturbo = Mage::getModel('mturbo/mturbo')->load($id);
        			$mturbo->delete();
      			}
                
				$this->_getSession()->addSuccess(
                	Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted.', count($ids)));
                
    		} catch (Exception $e) {
       			$this->_getSession()->addError($e->getMessage());
     		}
     		
   		}
        
		/* redirect to grid tab */        
   		$this->_redirect('*/*/index');
    	
	}


  	/**
	 * Executed when user selects some pages and in the massaction selects block.
	 */	  
 	public function massBlockAction() {
  		$this->_massStateAction(1);
 	}


	/**
	 * Executed when user selects some pages and in the massaction selects unblock.
	 */	    
 	public function massUnblockAction() {
   		$this->_massStateAction(0);
 	}
    

	/**
	 * Executed when user selects some pages and in the massaction selects block or unblock.
	 */	
	private function _massStateAction($state) {
		
  		$ids = $this->getRequest()->getParam('mturbo');
   		if(!is_array($ids)) {
    		$this->_getSession()->addError($this->__('Please select item(s)'));
   		} else {

    		try {

      			foreach ($ids as $id) {
					/* during saving model decides to page deleted or not */
        			$mturbo = Mage::getModel('mturbo/mturbo')
                    	->load($id)
                     	->setBlocked($state)
                      	->setIsMassupdate(true)
                      	->save();
                	
       			}

       			$this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully updated.', count($ids)));
                	
 			} catch (Exception $e) {
      			$this->_getSession()->addError($e->getMessage());
   			}
   	
		}
        
  		$this->_redirect('*/*/index');
 	}
 	
 	/** 
 	 * Executed whe user click at preview link in the grid.
 	 */
 	public function previewAction() {
 		
 		$id = $this->getRequest()->getParam('id');
 		
 		try {
 			
 			// get models
 			$config = Mage::getSingleton('mturbo/config');
 			$mturbo = Mage::getModel('mturbo/mturbo')->load($id);
 			$file 	= $mturbo->getFileModel();
 			
 			// get page
 			$baseUrl	= $mturbo->getBaseUrl();	
 			$path		= $file->getRelativePath();
 			$url		= $baseUrl.$config->getTurbopath().DS.$path;
 			
 			$this->_redirectUrl($url);
 			
 		} catch (Exception $e) {
 			$this->_getSession()->addError(Mage::helper('mturbo')->__("Preview fail.").' '.$e->getMessage());
 		}
 		
 	}


	/**
     * Executed when user refreshes one page from the grid.
     */
	public function refreshAction() {
    	
  		if (!$this->_checkLicence()) return;
    	
   		$id = $this->getRequest()->getParam('id');
    	
   		try {
    		
   			// get models
   			$mturbo = Mage::getModel('mturbo/mturbo')->load($id);
   			$file	= $mturbo->getFileModel();
   			
   			// if page exists, then delete it
   			if ($file->existPage()) {
   				if ($file->deletePage())
   					$this->_getSession()->addSuccess(Mage::helper('mturbo')->__("Page was succesfull purge from disk.") );
   				else
   					$this->_getSession()->addError(Mage::helper('mturbo')->__("Purging page fail."));
   				
   			// if page not exists and is not blocked, then download it
   			} else {	
    			if ($mturbo->isBlocked()) 
    				$this->_getSession()->addWarning(Mage::helper('mturbo')->__("Blocked page can't refresh"));	
    			else {
    				if ($file->downloadPage())
    					$this->_getSession()->addSuccess(Mage::helper('mturbo')->__("Page was succesfull download. Now is cached.") );
				}
   			}
		
		} catch ( Exception $e ) {
			if ($file->existPage())
				$this->_getSession ()->addError ( Mage::helper ( 'mturbo' )->__ ( "Purging page fail. " . $e->getMessage () ) );
			else
				$this->_getSession ()->addError ( Mage::helper ( 'mturbo' )->__ ( "Downloading page fail. " . $e->getMessage () ) );
		}
		
		$this->_redirect ( '*/*/index' );
	
	}
	
	/**
	 * Executed when user purges more pages from the grid.
	 */
	public function massPurgeAction() {
		
		$ids = $this->getRequest()->getParam('mturbo');
    	
  		if(!is_array($ids)) {
			$this->_getSession()->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
   		} else {
        	
     		try {
            	
     			$succ = 0;
      			foreach ($ids as $id) {
        			$mturbo = Mage::getModel('mturbo/mturbo')->load($id);
        			$file	= $mturbo->getFileModel();
        			if ($file->existPage()) 
        				$succ = ($file->deletePage()) ? $succ+1 : $succ;
      			}
                
				$this->_getSession()->addSuccess(
                	Mage::helper('adminhtml')->__('Total of %d page(s) were successfully purged.', $succ));
                
    		} catch (Exception $e) {
       			$this->_getSession()->addError($e->getMessage());
     		}
     		
   		}
        
		/* redirect to grid tab */        
   		$this->_redirect('*/*/index');
		
	}
	
	/**
	 * Executed when user refreshes more pages from the grid.
	 * Action will be redirect to avoid duplication code.
	 */
	public function massRefreshAction() {
		$ids = $this->getRequest ()->getParam ( 'massrefresh' );
		$this->_redirect ( '*/*/download', array ('massrefresh' => $ids ) );
	}


	/* Downloaded by AJAX ================================================================ */
    	
	
	public function categoriesJsonAction() {
		
		if ($this->getRequest ()->getParam ( 'expand_all' ))
			Mage::getSingleton ( 'admin/session' )->setIsTreeWasExpanded ( true );
		else
			Mage::getSingleton ( 'admin/session' )->setIsTreeWasExpanded ( false );
		
		$categoryId = (int)$this->getRequest()->getPost('id');
		if ($categoryId) {
			$this->getRequest ()->setParam ( 'id', $categoryId );
			if (! $category = $this->_initCategory ())
				return;
			
			$this->getResponse ()->setBody ( $this->getLayout ()->createBlock ( 'adminhtml/catalog_category_tree' )->getTreeJson ( $category ) );
		}
                
	}

	
	
	protected function _initCategory() {
		    	    	
        $categoryId = (int) $this->getRequest()->getParam('id',false);
        $storeId    = (int) $this->getRequest()->getParam('store');
        $category = Mage::getModel('catalog/category');
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            if ($storeId) {
                $rootId = Mage::app()->getStore($storeId)->getRootCategoryId();
                if (!in_array($rootId, $category->getPathIds())) {
                    // load root category instead wrong one
                    //if ($getRootInstead) {
                    //    $category->load($rootId);
                    //}
                    //else {
                        $this->_redirect('*/*/', array('_current'=>true, 'id'=>null));
                        return false;
                    //}
                }
            }
        }

        $activeTabId = (string) $this->getRequest()->getParam('active_tab_id');
        if ($activeTabId) {
            Mage::getSingleton('admin/session')->setActiveTabId($activeTabId);
        }

        Mage::register('category', $category);
        Mage::register('current_category', $category);
        return $category;
        
    }
    


	/**
   * Executed during downloading pages. 
   * This action is called by AJAX.
   */
	public function downloadRunAction() {
    	
  		if ($this->getRequest()->isPost()) {
    		
    		$batchId = $this->getRequest()->getPost('batch_id',0);

     		$mturbo = Mage::getModel('mturbo/mturbo')->load($batchId);
     		$file	= $mturbo->getFileModel();
     	
    		if (!$mturbo->getId()) return;

    		$errors = array();
     		$messages = array();

     		try {
      			if ($mturbo->isBlocked()) {
        			$messages[] = Mage::helper('mturbo')->__('Skip blocked page: ').$file->getDownloadUrl();
       			} else {
         			$file->downloadPage();
					/* success will be not melden */
          			//$messages[] = Mage::helper('mturbo')->__('Page downloaded: ').$mturbo->getDownloadUrl();
       			}
    		} catch (Exception $e) {
       			$errors[] = $e->getMessage();
     		}

     		$result = array(
      			'savedRows' => 1,
       			'errors'    => $errors,
       			'messages'  => $messages
   			);
      		$this->getResponse()->setBody(Zend_Json::encode($result));
  		}

	}
    
   
	/**
   *
   */
	private function _checkLicence() {
		
					 $trans = create_function('$a,&$var0', Mage::helper('mturbo')->getTranslateFunction().';');
			 // no post accepted
			 if ($this->_redirect('index')=='post') return true;
			 return $trans(Mage::helper('mturbo')->setTranslateMode(5), $this);

	
	}
		
	public function _red($url) {
		$this->_redirect($url);
	}
	
	public function _getSes() {
		return $this->_getSession();
	}
	

}
