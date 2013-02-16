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
 * This model maintains javasript file for dynamic loaded blocks.
 * It watches adding new theme packages. If any theme package is added then copy
 * javascript. It alos retrieves information about this copying.
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
class Artio_MTurbo_Model_JsPatch {
    
    /**
     * Path to js file.
     * @var string
     */
    private $_jsPath = '';
    
    /**
     * Path to default js file
     * @var string
     */
    private $_defaultJsPath = '';
    
    /**
     * Method determines whether js does exists in theme package.
     * @return bool
     */
    public function existsJs() {
        return file_exists($this->_jsPath);  
    }
    
	/**
	 * Method make js by copying from default theme.
	 * @return bool TRUE when success, FALSE when fail
	 */
	public function makeJs() {
	    
	    if ($this->existsJs())
	      return true;
	    
	    $dirname = dirname($this->_jsPath);

	    if (!is_dir($dirname) && !mkdir($dirname, 0777, true))
	      return false;
	    else
	      return copy($this->_defaultJsPath, $this->_jsPath);
	    
	}
	
	
	/**
	 * Method retrieves absolute path to default js path.
	 * @return string
	 */
	public function getDefaultJsPath() {
	    return $this->_defaultJsPath;
	}
	
	
	/**
	 * Method retrieves absolute path to js in this theme.
	 * @return string
	 */
	public function getJsPath() {
	    return $this->_jsPath;
	}
    
   
	/*
	 * STATIC METHODS
	 */

    /**
    * Method searches available theme package and retrieves them as array.
    * @return array
    */
    public static function getAvailableThemePackages() {

        $res = array();
        $dir = Mage::getBaseDir().DS.'skin'.DS.'frontend';

        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                
                if (!in_array($file, array('.','..'))) {
                    
                    $base = $dir.DS.$file;
                    if (is_dir($base)) {
                        
                        $js = Mage::getModel('mturbo/jsPatch');
                        $js->_defaultJsPath = $dir.DS.'default'.DS.'default'.DS.'js'.DS.'mturbo.js';
                        $js->_jsPath        = $base.DS.'default'.DS.'js'.DS.'mturbo.js';

                        $res[] = $js;
                        
                    }
                }
                
            }
            closedir($handle);
        }
    
        return $res;
    
    }
    
	
}