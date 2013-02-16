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
 * Download method for CURL.
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
class Artio_MTurbo_Model_DownloadMethods_Curl extends Artio_MTurbo_Model_DownloadMethods_Abstract
{

	/**
	 * Retrieves name of download method
	 */
	public function getName() {
		return Mage::helper('mturbo')->__('Using CURL PHP extensions');
	}
	
	
	/**
	 * Determines whether download method is ready for using.
	 * @return bool
	 */
	public function isReady() {
		if (function_exists('curl_init')) {
			$this->errorMsg = '';
			return true;
		} else {
			$this->errorMsg = Mage::helper('mturbo')->__('CURL is not installed');
			return false;
		}
	}

	/**
	 * Method download choosed page by url in argument and retrieves
	 * its contents.
	 * @param string $url
	 * @return string contents of downloaded page
	 */
	public function downloadPage($url) {
	
		if ($this->isReady()) {

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_TIMEOUT, 100);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$html = curl_exec($ch);
			if ($html===false)
				$this->errorMsg = curl_error($ch);
							
			curl_close($ch);
			return $html;
	  	}
	  	
	  	return '';
	  	
	}

}
?>
