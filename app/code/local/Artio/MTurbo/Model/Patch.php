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
 * The model holds information about using Mage path for direct access download method. 
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
class Artio_MTurbo_Model_Patch {


	/**
	 * Retrieves path to Mage Patch Template
	 * @return string path to Mage Patch Template
	 */
	public static function getMagePatchPath() {
		return Mage::getBaseDir().DS.'app'.DS.'code'.DS.'local'.DS.'Artio'.DS.'MTurbo'.DS.'Model'.DS.'patches'.DS.'mage.txt';
	}

	
	/**
	 * Retrieves path to file Mage.php
	 * @return string path to Mage.php
	 */
	public static function getMagePath() {
		return Mage::getBaseDir().DS.'app'.DS.'Mage.php';
	}


	/**
	 * Function removes Mage Patch from Mage.php
	 */
	public static function removePatch() {

		$magepath  =  self::getMagePath();
		$patchpath =  self::getMagePatchPath();
		
		$magepatch = @file_get_contents($patchpath);
		if ($magepatch=='')
			Mage::throwException(Mage::helper('mturbo')->__('Cannot read patch file').'('.$patchpath.')');
		
		$magephp = @file_get_contents($magepath);
		if ($magephp=='')
			Mage::throwException(Mage::helper('mturbo')->__('Cannot read Mage.php file'));
		if (strpos('ARTIO', $magephp)<0)
			Mage::throwException(Mage::helper('mturbo')->__('Patch already removed'));
		if (!is_writable($magepath))
			Mage::throwException(Mage::helper('mturbo')->__('Permission denied. Cannot write to Mage.php'));

		$newmagephp = str_replace($magepatch, "", $magephp);
		if (!file_put_contents($magepath, $newmagephp))
			Mage::throwException("Fail to saved Mage.php");
			
	}
	

	/**
	 * Function applies Mage Patch.
	 */
	public static function applyPatch() {
		
		$magepath 	= self::getMagePath();
		$patchpath	= self::getMagePatchPath();
		
		$magepatch = @file_get_contents($patchpath);
		if ($magepatch=='')
			Mage::throwException(Mage::helper('mturbo')->__('Cannot read patch file').'('.$patchpath.')');
		
		$magephp = @file_get_contents($magepath);
		if ($magephp=='')
			Mage::throwException(Mage::helper('mturbo')->__('Cannot read Mage.php file'));
		if (strpos('ARTIO', $magephp)>0)
			Mage::throwException(Mage::helper('mturbo')->__('Patch already used'));		
		if (!is_writable($magepath))
			Mage::throwException(Mage::helper('mturbo')->__('Permission denied. Cannot write to Mage.php'));
			
		$newmagephp = str_replace('class Mage {', "class Mage {\n".$magepatch."\n", $magephp);
		$newmagephp = str_replace("class Mage\n{", "class Mage\n{\n".$magepatch."\n", $newmagephp);

		if (!file_put_contents($magepath, $newmagephp))
			Mage::throwException("Fail to saved Mage.php");

	}
	
	/**
	 * Checks whether file Mage.php is patched
	 * @return bool TRUE when Mage.php is patched, otherwise FALSE
	 */
	public static function isPatched() {
		return method_exists('Mage', 'getStaticState') && method_exists('Mage', 'restoreState');
	}

}
?>
