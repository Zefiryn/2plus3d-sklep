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
 * Interface for all download methods.
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
abstract class Artio_MTurbo_Model_DownloadMethods_Abstract
{
	
	protected $errorMsg = '';

	/**
	 * Retrieves name of download method
	 */
	abstract function getName();
	
	
	/**
	 * Determines whether download method is ready for using.
	 * @return bool
	 */
	abstract function isReady();

	/**
	 * Method download choosen page by url in argument and retrieves
	 * its contents.
	 * @param string $url
	 * @return string contents of downloaded page
	 */
	abstract function downloadPage($url);
	
	
	/**
	 * Method retrieve error message.
	 * Error message can appearance only after download any page or after call function isReady.
	 * When page is downloaded successful is returned empty string.
	 * @return string
	 */
	public function getErrorMessage() {
		return $this->errorMsg;
	}

}
?>
