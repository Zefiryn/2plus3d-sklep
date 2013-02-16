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
 * The model holds information about using Layout patch, for load dynamic blocks.
 * Layout patch is located in class Mage_Core_Model_Layout.php.
 * 
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
class Artio_MTurbo_Model_LayoutPatch {


	/**
	 * Retrieves path to Mage Patch Template
	 * @return string path to Mage Patch Template
	 */
	public static function getLayoutPatchPath() {
		return Mage::getBaseDir().DS.'app'.DS.'code'.DS.'local'.DS.'Artio'.DS.'MTurbo'.DS.'Model'.DS.'patches'.DS.'layout.txt';
	}

	
	/**
	 * Retrieves path to file Mage.php
	 * @return string path to Mage.php
	 */
	public static function getLayoutPath() {
		return Mage::getBaseDir().DS.'app'.DS.'code'.DS.'core'.DS.'Mage'.DS.'Core'.DS.'Model'.DS.'Layout.php';
	}


	/**
	 * Function removes Mage Patch from Mage.php
	 */
	public static function removePatch() {

		$layoutpath  	=  self::getLayoutPath();
		$patchpath 		=  self::getLayoutPatchPath();
		
		$layoutpatch = @file_get_contents($patchpath);
		if ($layoutpatch=='')
			Mage::throwException(Mage::helper('mturbo')->__('Cannot read patch file').'('.$patchpath.')');
		
		$layoutphp = @file_get_contents($layoutpath);
		if ($layoutphp=='')
			Mage::throwException(Mage::helper('mturbo')->__('Cannot read Layout.php file'));
		if (strpos('ARTIO', $layoutphp)<0)
			Mage::throwException(Mage::helper('mturbo')->__('Patch already removed'));
		if (!is_writable($layoutpath))
			Mage::throwException(Mage::helper('mturbo')->__('Permission denied. Cannot write to Layout.php'));

		$newlayoutphp = str_replace($layoutpatch, "", $layoutphp);
		if (!file_put_contents($layoutpath, $newlayoutphp))
			Mage::throwException("Fail to saved Layout.php");
			
	}
	

	/**
	 * Function applies Mage Patch.
	 */
	public static function applyPatch() {
		
		$layoutpath = self::getLayoutPath();
		$patchpath	= self::getLayoutPatchPath();
		
		$layoutpatch = @file_get_contents($patchpath);
		if ($layoutpatch=='')
			Mage::throwException(Mage::helper('mturbo')->__('Cannot read patch file').'('.$patchpath.')');
		
		$layoutphp = @file_get_contents($layoutpath);
		if ($layoutphp=='')
			Mage::throwException(Mage::helper('mturbo')->__('Cannot read Layout.php file'));
		if (strpos('ARTIO', $layoutphp)>0)
			Mage::throwException(Mage::helper('mturbo')->__('Patch already used'));		
		if (!is_writable($layoutpath))
			Mage::throwException(Mage::helper('mturbo')->__('Permission denied. Cannot write to Layout.php'));
			
		// find position of function addBlock
		$basePos = strpos($layoutphp, 'public function addBlock');
		
		if ($basePos===false)
			Mage::throwException(Mage::helper('mturbo')->__("I can't find function addBlock"));
			
		// separe function addBlock from file
		$pos 	  = $basePos;
		$function = '';
		$beforeL  = true;
		$left	  =  0;
		while ($beforeL || $left>0) {
			$char 	   = $layoutphp[$pos++];
			$function .= $char;
			if ($char=='{') {
				$beforeL=false;
				$left++;
			} else if ($char=='}') {
				$left--;
			}
		}
		
		// replace
		$newfunction  = str_replace('return $block;', $layoutpatch."\n".'return $block;', $function);
		$newlayoutphp = str_replace($function, $newfunction, $layoutphp);

		if (!file_put_contents($layoutpath, $newlayoutphp))
			Mage::throwException("Fail to saved Layout.php");

	}
	
	/**
	 * Method determines whether, Magento must be patched.
	 * Only for version <= 1.4.0.1
	 */
	public static function needToPatch() {
		
		$versions = explode(".", Mage::getVersion());
		
		if (count($versions)==4) {
			return ($versions[1]=='3' || ($versions[1]=='4' && $versions[2]=='0'));
		}
		return false;
		
	}
	
	
	public function isWriteable() {
		return is_writeable($this->getLayoutPath());
	}
	
	/**
	 * Checks whether file Mage.php is patched
	 * @return bool TRUE when Mage.php is patched, otherwise FALSE
	 */
	public static function isPatched() {
		
		$layoutpath	= self::getLayoutPath();
		
		$content	= @file_get_contents($layoutpath);
		return (strpos($content, "ARTIO")!==false);
		
	}

}
?>
