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
 * Download method using sockets.
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
class Artio_MTurbo_Model_DownloadMethods_Socket extends Artio_MTurbo_Model_DownloadMethods_Abstract
{

	/**
	 * Retrieves name of download method
	 */
	function getName() {
		return Mage::helper('mturbo')->__('Create connection over sockets');
	}
	
	
	/**
	 * Determines whether download method is ready for using.
	 * @return bool
	 */
	function isReady() {
		$this->errorMsg = '';
		return true;
	}

	/**
	 * Method download choosen page by url in argument and retrieves
	 * its contents.
	 * @param string $url
	 * @return string contents of downloaded page
	 */
	function downloadPage($url) {
		
		/* output socket methods */
		$resultNumber=0;
	    $resultTest='';

	  	$request = preg_replace('/http(s){0,1}:\/\/[^\/]*/', '', $url);
						
		/* get host from url */
		$matches = array();
		preg_match('@^(?:http://)?([^/]+)@i', $url, $matches);
		$host = $matches[1];
	
		/* open socket */
	    $fp = @fsockopen($host, 80, $resultNumber, $errorMessage);
	    if (!$fp) {
	    	$this->errorMsg = $errorMessage;
	    } else {
	    	
	    	$html = '';

			/* build request */
	    	$out  = "GET $request HTTP/1.1\r\n";
	    	$out .= "Host: $host\r\n";
	   	 	$out .= "Connection: Close\r\n";
	   	 	$out .= "\r\n";

			/* post request */
	   	 	fwrite($fp, $out);

			/* get response */
	   	 	$header = "not yet";
	    	while (!feof($fp)) {            					
				$line=fgets($fp,128);
            	if ($line=="\r\n" && $header=="not yet") {
                	$header = "passed";
            	}
            		
				if ($header=="passed") {
                	$html.=$line;
            	}
	    	}
	    	
	    	fclose($fp);
	    			
	    	$first = strpos($html, '<!DOCTYPE');
	    	$last  = strpos($html, '</html>')+7;
	    	return substr($html, $first, $last-$first);
	    						    			
	    }
		
	}

}
?>
