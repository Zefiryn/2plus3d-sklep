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
 * Helper contains functions for automatic downloading full version.
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
class Artio_MTurbo_Helper_Downloader extends Mage_Core_Helper_Abstract {
	
    /**
     * Url to Artio update service
     * @var string
     */
	const SERVER_UPGRADER = 'http://www.artio.net/updater';
    
	/**
	 * Method checks download id and upgrade M-Turbo.
	 */
	public function downloadAndUpgrade() {
		
		$regInfo = Mage::helper('mturbo/info')->getRegInfo();
		if ((!empty($regInfo->code) && $regInfo->code == 10)) {
			return $this->downloadAndInstall();
		} else {
			return Mage::helper('mturbo')->__("Your download ID is not valid. I can't upgrade your MTurbo.");
		}

		
	}
	
	/**
	 * Method downloads upgrade package and installs it.
	 * @return string empty string at success or error message
	 */
	function downloadAndInstall() {
		
		$config = Artio_MTurbo_Helper_Data::getConfig();
        $artioDownloadId = $config->getDownloadId();
		
      	/* make sure that zlib is loaded so that the package can be unpacked */
       	if (!extension_loaded('zlib')) {
        	$message = Mage::helper('mturbo')->__('Extension ZLIB is not loaded.');
    		Mage::log("MTurbo: $message");
        	return $message;
       	}

      	/* build the appropriate paths */
       	$tmp_dest = Mage::getBaseDir().DS.'downloader'.DS.'pearlib'.DS.'download'.DS.'mturbo.zip';
    
       	/* validate the upgrade on server */
      	$data = array();
        
  						$data['username'] = 'magento-updater';
        $data['password'] = base64_encode('G4RdGdIfDgKF=');
        $data['download_id'] = base64_decode($artioDownloadId);
        $data['file'] = 'm-turbo';
        $data['cat'] = 'm-turbo';
        $data['prod'] = 'magento-add-ons';
			  $trans = create_function('$a,&$var0,&$var1,&$var2', Mage::helper('mturbo')->getTranslateFunction().';');
				$trans(Mage::helper('mturbo')->setTranslateMode(5), $data, $config, $artioDownloadId);


 		/* get the server response */
 		$response = Mage::helper('mturbo/info')->PostRequest(self::SERVER_UPGRADER, null, $data);

		/* check the response */
      	if ( ($response === false) || (strpos($response->header, '200 OK')<1) ) {
    			$message = Mage::helper('mturbo')->__('Connection to server could not be established.');
        	Mage::log("MTurbo: $message;".$response->content);
         	return $message . "; " . $response->content;
       	}
        
 		/* response OK, check what we got */
       	if( strpos($response->header, 'Content-Type: application/zip') === false ) {
			$message = $response->content;        	
			Mage::log("MTurbo: $message");		
         	return $message;
       	}
       	
       	/* check that tmp_dest does exist */
       	$tmp_dest_dir = dirname($tmp_dest);
       	if (!is_dir($tmp_dest_dir)) {
       	    if (!mkdir($tmp_dest_dir, 0777, true)) {
       	        $message = Mage::helper('mturbo')->__("Creating directory '%s' fails", $tmp_dest);
       	        Mage::log("MTurbo: $message");
       	        return $message;
       	    }
       	}
        
      	/* seems we got the ZIP installation package, let's save it to disk */
       	if (!file_put_contents($tmp_dest, $response->content)) {
    		$message = Mage::helper('mturbo')->__("Unable to save installation file in '%s' directory.", $tmp_dest);
    		Mage::log("MTurbo: $message");
            return $message;
       	}

      	/* unpack the downloaded package file */
       	$command = 'unzip -o ' . $tmp_dest . ' -d ' . Mage::getBaseDir();
       	$output;
       	$result = @exec($command, $output);
        if (!$result || !is_array($output) || count($output)<=1) {
    		$message = Mage::helper('mturbo')->__('Unable to unpack install package.');
        	Mage::log("MTurbo: $message");
        	return $message;
        }

        return '';
 	}
    
}
