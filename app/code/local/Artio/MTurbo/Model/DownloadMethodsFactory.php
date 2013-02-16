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
 * The model holds codes of different methods of downloading sites.
 * It is appropriate to create as Singleton.
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
class Artio_MTurbo_Model_DownloadMethodsFactory
{

	/* constant for possible methods of downloading sites */	
	private static $defaultMethod	= 'socket';
	private static $possibleMethods = array('filegetcontents', 'socket', 'curl', 'direct');
	
	/**
	 * Determines whether method exists.
	 *
	 * @return bool
	 */
	public function exists($code) {
		return in_array($code, self::$possibleMethods);
	}

	
	/**
	 * Function retrieves code of default method
	 *
	 * @return string 
	 */	
	public function getDefaultMethod() {
		return self::$defaultMethod;
	}
	
	
	/**
	 * Function retrieves download method by code.
	 * @param string $code
	 * @return Artio_MTurbo_Model_Config_DownloadMethods_Abstract
	 */
	public function getMethod($code) {
		
		if (!$this->exists($code))
			return null;
			
		return Mage::getSingleton('mturbo/downloadMethods_'.$code);
			
	}
	
	
	/**
	 * Function retrieves list of all possible methods.
	 * @return array possible methods (code => method name)
	 */
	public function getList() {
		
		$result = array();
		
		foreach (self::$possibleMethods as $possibleMethod) {
			
			$method = $this->getMethod($possibleMethod);
			if ($method)
				$result[$possibleMethod] = $method->getName();
			
		}
		
		return $result;
		
	}

}
?>
