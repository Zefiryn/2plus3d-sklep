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
 * Download methods using PHP function file_get_contents.
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
class Artio_MTurbo_Model_DownloadMethods_Filegetcontents extends Artio_MTurbo_Model_DownloadMethods_Abstract
{

	/**
	 * Retrieves name of download method
	 */
	function getName() {
		return Mage::helper('mturbo')->__("Using function 'file_get_contents'");
	}
	
	
	/**
	 * Determines whether download method is ready for using.
	 * @return bool
	 */
	function isReady() {
		
		if (ini_get('allow_url_fopen') == '1') {
			$this->errorMsg = '';
			return true;
		} else {
			$this->errorMsg = Mage::helper('mturbo')->__("Setting 'allow_url_fopen' is disabled.");
			return false;
		}

	}

	/**
	 * Method download choosen page by url in argument and retrieves
	 * its contents.
	 * @param string $url
	 * @return string contents of downloaded page
	 */
	function downloadPage($url) {
		if ($this->isReady())
	    	return file_get_contents($url);
	    	
	    return '';
	}

}
?>
