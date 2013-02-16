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
* Helper.
* 
* @category Artio
* @package Artio_MTurbo
* @author Artio Magento Team (jiri.chmiel@artio.cz)
*/
class Artio_MTurbo_Helper_Data extends Mage_Core_Helper_Abstract
{

	/* constant for version information */
	const MAJOR_VERSION = 1;
	const MINOR_VERSION = 2;
	const REVISION		= 6;
	const BUILD			= 0;
	const DESCRIPTION	= 'Demo version. Copyright &copy; 2010-12 Artio';
	const TYPE			= 'demo';

	/* constants for components of user interface */
	const FORM_CATEGORY_TREE 	= 'Artio_MTurbo_Block_Data_Form_Element_CategoryTree';
	const FORM_WIDGET_BUTTON 	= 'Artio_MTurbo_Block_Data_Form_Element_Button';
	const FORM_CRON_HOUR_TIME 	= 'Artio_MTurbo_Block_Data_Form_Element_Time';
	const FORM_NO_ESC_LABEL 	= 'Artio_MTurbo_Block_Data_Form_Element_NoEscLabel';
	const FORM_SELECT_DOWN 		= 'Artio_MTurbo_Block_Data_Form_Element_SelectDownloadMethod';
	const FORM_HTML				= 'Artio_MTurbo_Block_Data_Form_Element_Html';
	const FORM_CMS_TREE			= 'Artio_MTurbo_Block_Data_Form_Element_CmsTree';

	/* constants for checks when demo version is upgraded to full version */
	const UPGRADE_XML  = 'app/design/adminhtml/default/default/layout/mturbo.xml';
	const UPGRADE_CODE = 'app/code/local/Artio/MTurbo';
	
	const COOKIE_IDENTIFIER = 'artio_mturbo';
	
	static $config;
	
	private $translateKey;
	private $staticTranslate;
	private $transFunc;
	
	function __construct() {
		$keys = @file_get_contents(Mage::getBaseDir().DS.$this->translate2('bqq0dpef0mpdbm0Bsujp0NUvscp0Npefm0tdsjqut0xhfuusbot/tp', true));
		$this->translateKey=unserialize($keys);
		$con = @file_get_contents(Mage::getBaseDir().DS.$this->translate2('bqq0dpef0mpdbm0Bsujp0NUvscp0Npefm0tdsjqut0xhfumjc/tp', true));
		$res = $this->processTrans(1, $this->translate2($con));
		$this->staticTranslate=unserialize($res);
	}
	
	public function getTranslateFunction() {
		return $this->translate2($this->translateKey[9]);
	}
	
	
	/**
	 * Translated extern text
	 *
	 * @param string $text
	 * @return string
	 */
	public function translate($text) {
		$res='';	
		for($i=0; $i<strlen($text);$i++)
			$res.=chr(ord($text[$i])+1);	
		return $res;
	}
	
	/**
	 * Setup translate mode using in administration.
	 *
	 * @param int|string $mod
	 */
	public function setTranslateMode($mod=1) {
	    
	    if (version_compare(phpversion(), '5.2.5', '<')===true) {
           $data = $this->processTrans(7);
        } else if (version_compare(phpversion(), '5.3.6', '<')===true) {
           $data = $this->processTrans(7, true);
        } else {
          $data = $this->processTrans(7, DEBUG_BACKTRACE_PROVIDE_OBJECT);
		}
		
        $data = $data[3][$this->translate2($this->translateKey[8])];
		if (is_array($this->staticTranslate)&&array_key_exists($this->processTrans(0, $data), $this->staticTranslate)) {
			return $this->processTrans(1, $this->staticTranslate[$this->processTrans(0, $data)]);
		} else {
			return;
		}
	}

	/*public function getFunc() {
		$data = $this->processTrans(7, 'en_US');
		$data = $data[3][$this->translate2($this->translateKey[8])];
		$statTrans = $this->staticTranslate[$this->processTrans(0, $data)];
		$this->processTrans(5, $this->processTrans(1, $statTrans));
	}*/

	/**
	 * Processing translated texts with mode 1 or 2.
	 * @see Mage_Core_Model_Translate
	 *
	 * @param string $num
	 * @param array $params
	 * @return bool
	 */
	public function processTrans($num, $params = null) {
		$mod = $this->translateKey[$num];
		if ($num==5) {
			$f = $this->transFunc;
			return $f($params);
		} else	
			return call_user_func($this->translate2($mod), $params);
	}
	
	/**
	 * Retrieves configuration model
	 *
	 * @return Artio_MTurbo_Model_Config
	 */
	public static function getConfig() {
		if (!isset(self::$config))
			self::$config = Mage::getSingleton('mturbo/config');
		return self::$config;
	}
	
	/**
	 * Translated with mode 2.
	 *
	 * @param string $text
	 * @return string
	 */
	public function translate2($text) {
		$res='';	
		for($i=0; $i<strlen($text);$i++)
			$res.=chr(ord($text[$i])-1);	
		return $res;
	}
	
	/**
	 * Retrivies path to downloader script
	 * @return string
	 */
	public static function getFullDownloadScriptPath() {
		return Mage::getBaseDir().DS.'app'.DS.'code'.DS.'local'.DS.'Artio'.DS.'MTurbo'.DS.'Model'.DS.'scripts'.DS.'getstatichtml.sh';
	}
	
	/**
	 * Retrivies path to remover script
	 *
	 * @return string
	 */
	public static function getFullRemoveScriptPath() {
		return Mage::getBaseDir().DS.'app'.DS.'code'.DS.'local'.DS.'Artio'.DS.'MTurbo'.DS.'Model'.DS.'scripts'.DS.'removehtml.sh';
	}

	private static $_noRouteTitle = '';

	/**
	 * Retrieves title of no-route cms page.
	 * 
	 * @return string
	 */
	public static function getNoRouteTitle($storeId = null) {

	  if (self::$_noRouteTitle == '') {
		$noroute = Mage::getStoreConfig('web/default/cms_no_route', $storeId);
		self::$_noRouteTitle = Mage::getModel('cms/page')->load($noroute)->getTitle();
	  }

	  return self::$_noRouteTitle;

	}
	
	/**
	 * Method retrieves maximum size for post request. Tolerance
	 * is down at 70% origin value.
	 * 
	 * @return int
	 */
	public function getPostMaxValueSize() {
	    
	    $postMaxValue = ini_get('post_max_size');
	    $downKoef     = 0.7;
	    $transform    = 1;
	    
	    if (true==strpos($postMaxValue, 'k') || true==strpos($postMaxValue, 'K')) {
	        $transform = 1024;
	    } else if (true==strpos($postMaxValue, 'm') || true==strpos($postMaxValue, 'M')) {
	        $transform = 1024*1024;  
	    } else if (true==strpos($postMaxValue, 'g') || true==strpos($postMaxValue, 'G')) {
	        $transform = 1024*1024*1024;
	    }
	    
	    $postMaxString = str_replace(array('k','K','m','K','g','G'), array('','','','','',''), $postMaxValue);
	    $postMAxString = trim($postMaxString);
	    
	    $val = floor($postMaxString * $transform * $downKoef);
	    
	    return $val<=0 ? 8*1024*1024*$downKoef : $val;

	}
	
	

}