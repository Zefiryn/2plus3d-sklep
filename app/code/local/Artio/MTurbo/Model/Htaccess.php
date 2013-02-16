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
 * The model holds information about htaccess configuration and contains manipulation methods. 
 * 
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
class Artio_MTurbo_Model_Htaccess
{

    /* constats for path to htaccess templates */
    const CONFIG_PATH_TO_HTACCESS           = 'htaccess/htaccess.txt';
    const CONFIG_PATH_TO_HTACCESSWEBSITE    = 'htaccess/htaccesswebsite.txt';
    const CONFIG_PATH_TO_HTACCESSSTORE      = 'htaccess/htaccessstore.txt';
    const CONFIG_PATH_TO_HTACCESSSIDE       = 'htaccess/htaccessside.txt';
    
    /* constants for finding and replacing text in htaccess files */
    const CONFIG_HTACCESS_ROOTPATH     = '$ROOTPATH';
    const CONFIG_HTACCESS_TURBOPATH    = '$TURBOPATH';
    const CONFIG_HTACCESS_EXTCONSTANT  = '$EXTENSION';
    const CONFIG_HTACCESS_SUBBASE      = '$SUBBASE';
    const CONFIG_HTACCESS_STORE        = '$STORES';
    const CONFIG_HTACCESS_WEBSITE      = '$WEBSITES';
    const CONFIG_HTACCESS_STORENAME    = '$STORENAME';
    const CONFIG_HTACCESS_STORECODE    = '$STORECODE';
    const CONFIG_HTACCESS_SERVER       = '$SERVER';
    
    const CONFIG_HTACCESS_FINDKEY       = 'M-Turbo Accelleration';
    const CONFIG_HTACCESS_FINDBASE      = 'RewriteBase';
    const CONFIG_HTACCESS_FINDENGINEON  = 'RewriteEngine on';
    const CONFIG_HTACCESS_STARTMTURBO   = "M-Turbo Accelleration";
    const CONFIG_HTACCESS_ENDMTURBO     = "End M-Turbo";

    
    /**
     * Website code
     * @var string
     */
    private $websitecode    = null;
    
    
    /**
     * Configuration for website
     * @var Artio_MTurbo_Model_Config_Website
     */
    private $websiteconfig  = null;
    
    
    /**
     * Magento website model.
      * @var Mage_Core_Model_Website
      */
    private $websitemodel   = null;
    
    /**
     * Set website code.
     * @param string $websitecode
     */
    public function setWebsiteCode($websitecode) {
        
        $this->websitecode      = $websitecode; 
        $this->websiteconfig    = Mage::getSingleton('mturbo/config')->getWebsiteConfig($websitecode);                          
        $this->websitemodel     = Mage::getModel('core/website')->load($websitecode);
        
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
     * Retrieves path to base .htaccess.
     * This path depends on selected website.
     * @return string
     */
    public function getPathToBaseHtaccess() {
        return str_replace('//', '/', $this->websiteconfig->getBaseDir().DS.'.htaccess');
    }
    
    
    /**
     * Retrieves full path to template of sides htaccess file.
     * @return string full path to template of sides htaccess file
     */
    public static function getSideHtaccessTemplatePath() {
        return str_replace(array('/','//'), array(DS,DS), Mage::getBaseDir().DS.'app'.DS.'code'.DS.'local'.DS.'Artio'.DS.'MTurbo'.DS.'Model'.DS.self::CONFIG_PATH_TO_HTACCESSSIDE);
    }
    

    /**
     * Retrieves full path to template of mainly htacess file.
     * @return string full path to template of mainly htaccess file
     */
    public static function getBaseHtaccessTemplatePath() {
        return str_replace(array('/','//'), array(DS,DS), Mage::getBaseDir().DS.'app'.DS.'code'.DS.'local'.DS.'Artio'.DS.'MTurbo'.DS.'Model'.DS.self::CONFIG_PATH_TO_HTACCESS);
    }

    /**
     * Retrieves full path to template of store htacess file.
     * @return string full path to template of store htaccess file
     */
    public static function getWebsiteHtaccessTemplatePath() {
        return str_replace(array('/','//'), array(DS,DS), Mage::getBaseDir().DS.'app'.DS.'code'.DS.'local'.DS.'Artio'.DS.'MTurbo'.DS.'Model'.DS.self::CONFIG_PATH_TO_HTACCESSWEBSITE);
    }
    
    
    /**
     * Retrieves full path to template of store htacess file.
     * @return string full path to template of store htaccess file
     */
    public static function getStoreHtaccessTemplatePath() {
        return str_replace(array('/','//'), array(DS,DS), Mage::getBaseDir().DS.'app'.DS.'code'.DS.'local'.DS.'Artio'.DS.'MTurbo'.DS.'Model'.DS.self::CONFIG_PATH_TO_HTACCESS_STORE);
    }

    
    /**
     * Method determines whether .htaccess was edited by MTurbo.
     * @return bool true when was edited, otherwise retrieves false
     */
    public function isEditedByMTurbo() {
        
        /* read content */
        try {
            $content = $this->_getContentHtaccess();
        } catch (Exception $e) {
            return false;
        }
        
        return ($content && (strpos($content, self::CONFIG_HTACCESS_FINDKEY)!==false));
        
    }
    
    
    /**
     * Method does action on htaccess for all websites.
     * @param string $action ('rebuild','remove')
     */
    public function actionAllWebsites($action='rebuild') {
        
        // getting websites
        $websiteCodes = array();
        $websites = Mage::getModel('core/website')->getCollection()->load();
        foreach ($websites->getItems() as $website)
            $websiteCodes[$website->getCode()] = $website->getName();
        
        /* rebuild htaccess of all codes in $websiteCodes */
        foreach ($websiteCodes as $code=>$name) {
            try {
                if ($action=='remove')
                    $result = Mage::getModel('mturbo/htaccess')->setWebsiteCode($code)->removeMTurboDirectives();
                else {
                    $result = Mage::getModel('mturbo/htaccess')->setWebsiteCode($code)->rebuildHtaccess();
                }
            } catch (Exception $e) {
                
            }
        }
  
    }

    /**
     * Function rebuilds main htaccess. At first remove MTurbo directive, if any.
     * And then inserts MTurbo directives into htaccess.
     */
    public function rebuildHtaccess($makeBackup=true) {
        
        if ($makeBackup)
            $this->makeBackup();

        if (!$this->removeMTurboDirectives())
            Mage::throwException(Mage::helper('mturbo')->__("Removing MTurbo directives from htaccess fail.")); 
        
        if (!$this->insertMTurboDirectives())
            Mage::throwException(Mage::helper('mturbo')->__("Inserting MTurbo directives into htaccess fail."));

        $this->copySideHtaccess();  

    }

    
    /**
     * Function inserts MTurbo directives in to main htaccess.
     * @param bool $makeBackup when TRUE will makes backup
     * @return bool TRUE when success, FALSE when fail
     */
    public function insertMTurboDirectives($makeBackup=false) {
        
        if (!isset($this->websiteconfig))
            Mage::throwException(Mage::helper('mturbo')->__("Htaccess model has not assigned website"));

        if ($makeBackup)
            $this->makeBackup();
            
        /* get configuration */
        $config = Mage::getSingleton('mturbo/config');
        
        /* read content */
        $content = $this->_getContentHtaccess();

        /* checks for readable */
        if (!$content) {
            Mage::log("MTurbo: I can't read content of htaccess.");
            Mage::throwException(Mage::helper('mturbo')->__("Unable to retrieve the contents of htaccess."));   
        }

        $htaccesPath            = $this->websiteconfig->getBaseDir().DS.'.htaccess';
        $htaccesTemplate        = file_get_contents($this->getBaseHtaccessTemplatePath());

        /* checks whether template is readed */
        if (!$htaccesTemplate)
            Mage::throwException(Mage::helper('mturbo')->__("Inserting MTurbo directives fail. Htaccess template not found."));

        $rules = '';
        $mapp = $this->_getBaseDirWebsiteMapping();
        if (!is_array($mapp))
          return true;

        $isonewebsite = count($mapp[$this->websiteconfig->getBaseDir()])<2;
        foreach ($mapp[$this->websiteconfig->getBaseDir()] as $code)
          $rules .= $this->_getHtaccessForWebsite($code, $config, $isonewebsite);

        $htaccesTemplate = str_replace(self::CONFIG_HTACCESS_WEBSITE, $rules, $htaccesTemplate);
            
        /* searching rewrite engine on and position to next new line */
        $posEngineOn = strpos($content, self::CONFIG_HTACCESS_FINDENGINEON);
        $posEngineNL = strpos($content, "\n", $posEngineOn);

        /* searching rewrite base and position to next new line */
        $posBase   = strpos($content, self::CONFIG_HTACCESS_FINDBASE);
        $posBaseNL = strpos($content, "\n", $posBase);

        /* compute insert position */
        $position = ($posBaseNL > $posEngineNL) ? $posBaseNL : $posEngineNL;
            
        /* insert htaccess template into original htaccess */
        $content = Mage::helper('mturbo/functions')->str_insert($htaccesTemplate, $content, $position+1);

        /* save htaccess file */
        return file_put_contents($htaccesPath, $content);

    }

    private function _getHtaccessForWebsite($websitecode, $config, $onewebsite) {

        /* load template */
        $htaccesTemplate = file_get_contents(str_replace('/',DS,self::CONFIG_PATH_TO_HTACCESSWEBSITE), true);
        if ($htaccesTemplate == false) {
            Mage::throwException("I can't read added .htaccesswebsite");
        }

        /* get htacess model */
        $htaccesModel = Mage::getModel('mturbo/htaccess');
        $htaccesModel->setWebsiteCode($websitecode);

        /* get default store */
        $defaultStore = $htaccesModel->websitemodel->getDefaultStore();
        
        /* build htaccess for stores */
        $htaccessStores = '';
        foreach ($htaccesModel->websitemodel->getStores() as $store)
            if ($htaccesModel->websiteconfig->isStoreViewEnabled($store->getCode()))
                $htaccessStores .= $htaccesModel->_getHtaccessForStore($store, $config);
            
        /* get server name */
        $serverName = $config->getWebsiteConfig($websitecode)->getServerName();
        if (!$serverName) {
          $serverName = Mage::helper('mturbo/website')->getServerName($defaultStore->getCode());
        }
        /* transform for regexp */
        $serverName = str_replace('.', '\.', $serverName);

        /* replace variables to path to turbocache directory */
        $removed = array(
                self::CONFIG_HTACCESS_SERVER,
                self::CONFIG_HTACCESS_STORE,
                self::CONFIG_HTACCESS_ROOTPATH,
                self::CONFIG_HTACCESS_SUBBASE,
                self::CONFIG_HTACCESS_EXTCONSTANT,
                self::CONFIG_HTACCESS_STORENAME,
                self::CONFIG_HTACCESS_STORECODE,
                self::CONFIG_HTACCESS_TURBOPATH
            );

        $placed = array(
                $serverName,
                $htaccessStores,
                str_replace('//', '/', $htaccesModel->websiteconfig->getBaseDir().DS.$config->getTurbopath()),
                Mage::helper('mturbo/website')->getSubbase($defaultStore),
                Mage::helper('mturbo/website')->getExtension($defaultStore),
                $defaultStore->getName(),
                $defaultStore->getCode(),
                $config->getTurbopath()
            );
            
        /* replacing variables */
        $result = str_replace($removed, $placed, $htaccesTemplate)."\n";

        /* if system in onewebsite mode or if htaccess contain only one website, then dont insert server */
        if ($onewebsite) {
            $result = preg_replace('/RewriteCond %{SERVER_NAME}[^\r\n]*/', '', $result);
        }

        return $result;

    }
    
    
    /**
     * Function retrieves htaccess for store
     */
    private function _getHtaccessForStore($store, $config) {
        
        /* load template */
        $htaccesContent = file_get_contents(str_replace('/',DS,self::CONFIG_PATH_TO_HTACCESSSTORE), true);
        if ($htaccesContent == false) {
            Mage::throwException("I can't read added .htaccessstore");
        }
        
        /* replace variables */
        $removed = array(
            self::CONFIG_HTACCESS_ROOTPATH,
            self::CONFIG_HTACCESS_SUBBASE,
            self::CONFIG_HTACCESS_EXTCONSTANT,
            self::CONFIG_HTACCESS_STORENAME,
            self::CONFIG_HTACCESS_STORECODE
        );
        
        $placed = array(
            str_replace('//', '/', $this->websiteconfig->getBaseDir().DS.$config->getTurbopath()),
            Mage::helper('mturbo/website')->getSubbase($store),
            Mage::helper('mturbo/website')->getExtension($store),
            $store->getName(),
            $store->getCode()
        );
        
        return str_replace($removed, $placed, $htaccesContent);
        
    }


    /**
     * Function removes MTurbo directives from main htaccess.
     * @param bool $makeBackup when TRUE will makes backup
     * @return bool TRUE when success, FALSE when fail
     */
    public function removeMTurboDirectives($makeBackup=false) {
        
        if (!isset($this->websiteconfig))
            Mage::throwException(Mage::helper('mturbo')->__("Htaccess model has not assigned website"));
        
        if ($makeBackup)
            $this->makeBackup();

        $htaccesPath = $this->getPathToBaseHtaccess();

        /* read content */
        $content = $this->_getContentHtaccess();

        /* checks for readable */
        if (!$content) {
            Mage::log("MTurbo: I can't read content of htaccess.");
            Mage::throwException(Mage::helper('mturbo')->__("Unable to retrieve the contents of htaccess."));   
        }

        /* searching start MTurbo directive */
        $startpos = strpos($content, self::CONFIG_HTACCESS_STARTMTURBO);
    
        /* if htaccess contains no start MTurbo directive */    
        if ($startpos <= 0)
            return true;

        /* returns back to two rows, because start directive begin at two rows */
        for ($new=0;$new<2;$startpos--)
            if ($content[$startpos] == "\n") $new++;

        /* searching end MTurbo directive */
        $endpos = strpos($content, self::CONFIG_HTACCESS_ENDMTURBO);

        /* if htaccess contains no end MTurbo directive, we have problem */
        if ($endpos < 0)
            Mage::throwException(Mage::helper('mturbo')->__("Removing MTurbo directives fail. Terminating MTurbo directive not found. The htaccess may be corrupted."));

        /* shift about length of end MTurbo directive */
        $endpos += strlen(self::CONFIG_HTACCESS_ENDMTURBO);

        /* searching, removing MTurbo directives and saving htaccess */
        $old  = substr($content, $startpos, $endpos-$startpos);
        $pure = str_replace($old, '', $content);
        return file_put_contents($htaccesPath, $pure);

    }


    /**
     * Function retrieves content of main htaccess
     *
     * return string content of htaccess or FALSE when FAIL
     */
    private function _getContentHtaccess() {

        $htaccesPath = $this->getPathToBaseHtaccess();

        /* checks for exists and writable */
        if (!file_exists($htaccesPath)) {
            Mage::log("MTurbo: I can't remove MTurbo directives. $htaccesPath does not exist.");
            Mage::throwException(Mage::helper('mturbo')->__("Htaccess does not exist."));
        }
        else if(!is_writeable($htaccesPath)) {
            Mage::log("MTurbo: I can't remove MTurbo directives. $htaccesPath is not writable.");
            Mage::throwException(Mage::helper('mturbo')->__("Htaccess is not writable."));
        }

        /* read content */
        return file_get_contents($htaccesPath);

    }
    

    /**
     * Function makes backup of htaccess. 
     * If it fails the backup will be not created.
     */ 
    public function makeBackup() {
        
        $config = Mage::getSingleton('mturbo/config');
        
        /* make back only if is enabled in configuration */
        if ($config->getEnabledHtaccessBackup()) {
        
            $backupNum  = $config->getNumberOfHtaccessBackups();
            $backupPath = $this->websiteconfig->getBaseDir().DS.'.htaccess.bak';
        
            /* search free backup slot */
            for ($i=0; $i<$backupNum; $i++)
                if (!file_exists($backupPath.$i)) break;

            /* if backup slot is full, shift backups and save backup at $backupNum-1 */         
            if ($i==$backupNum) {
                $i = $backupNum-1;
                for ($j=0; $j<$backupNum-1; $j++) {
                    if ($j==0) @unlink($backupPath.$j);
                    @rename($backupPath.($j+1), $backupPath.$j);
                }
            }

            $htaccessPath = Mage::getBaseDir().DS.'.htaccess';
            $backupPath   = $backupPath.$i;

            if (!copy($htaccessPath, $backupPath))
                Mage::log("I can't makes backup of htaccess");
                
        }

    }


    /**
     * Copies side htaccess file to the directory $path for access from the network. 
     * If $path is empty takes the path to turbocache directory from the current settings
     *
     * @param string $path 
     * @return bool TRUE when success, otherwise FALSE
     */
    public function copySideHtaccess($path='') {
       
        $config = Mage::getSingleton('mturbo/config');
    
        if ($path=='')
            $path = Mage::getBaseDir().DS.$config->getTurbopath();

        /* destination path is path to directory + filename */      
        $dest = $path.DS.'.htaccess';

        if (!file_exists($path))
          mkdir($path, 0775, true);

        /* source path is path to templates within module */
        $source = self::getSideHtaccessTemplatePath();

        if (!file_exists($source)) {
            Mage::log("MTurbo: I can't copy side htaccess. $source does not exists.");      
        } else if (!file_exists($path)) {
            Mage::log("MTurbo: I can't copy side htaccess. $path does not exists.");        
        } else if (file_exists($dest) && !is_writable($dest)) {
            Mage::log("MTurbo: I can't copy side htaccess. $dest exists but is not writable.");     
        } else {
            return @copy($source, $dest);       
        }

        /* if we are here something failed */ 
        return FALSE;

    }

    /**
     * Function retrieves associated array base_dir => array(websitecodes).
     *
     * @return array
     */
    private function _getBaseDirWebsiteMapping() {
    
      // prepare instance of configuration model
      // prepare result
      $config = Mage::getSingleton('mturbo/config');
      $result = array();

      // for each websites in magento
      $websites = Mage::getModel('core/website')->getCollection()->load();
      foreach ($websites->getItems() as $website) {

        // get code and load configuration
        $code = $website->getCode();
        $websiteConfig = $config->getWebsiteConfig($website->getCode());

        // website will be processed only when enabled is
        if ($websiteConfig->getEnabled()) {

            $basedir = $websiteConfig->getBaseDir();
            if (!isset($result[$basedir]))
                $result[$basedir] = array();

            $result[$basedir][] = $code;
    
        }

      }

      return $result;

    }

}
?>
