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
 * Helper contains varioused features missed in PHP.
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
class Artio_MTurbo_Helper_Functions extends Mage_Core_Helper_Abstract
{

	/**
	 * Function inserts string into another string to the chosen place.
	 *
	 * @param string $instertstring inserted string
	 * @param string $intostring subject
	 * @param int $offset offset, must be in interval [0, strlen($intostring)]
	 * @return string substr(intostring 0, $offset) . $insertstring . substr($intostring, $offset), or FALSE when fail
	 */
	public function str_insert($insertstring='', $intostring='', $offset='') {

   		$part1 = substr($intostring, 0, $offset);
   		$part2 = substr($intostring, $offset);

		if ($part1 == false || $part2 == false) 
			return false;
 		else
			return $part1 . $insertstring . $part2;
 
	}


	/**
	 * Function splits string to array by delimiter.
	 * Delimiter at first position will be ignored.
	 *
	 * @param string input string
	 * @param string delimiter
	 * @return array
	 */
	public function str_to_array($str, $delimiter=',') {
		
		if (!is_string($str) || $str==='')
			return array();			

		if (strpos($str, $delimiter)===0)
			$str = substr($str, 1);

		return explode($delimiter, $str);	
	
	}
	
	/**
	 * Function retrieves unique_array from array or string,
	 * where are values delimiteted by delimiter.
	 * 
	 * @param string|array $data
	 * @param string $delimiter
	 */
	public function make_unique_array($data, $delimiter=',') {
		
		if (is_array($data))
			return array_unique($data);
		else if (is_string($data))
			return array_unique(self::str_to_array($data, $delimiter));
		else
			return array();
	
	}
	
	
	/** 
	 * Function deletes directory recursive.
	 *
	 * @param $dir directory to delete
	 * @param $pattern
	 */	
	function unlink_recursive($dir, $match='/.*\.html$/', $includeDirs=false) {

		if (is_file($dir) && preg_match($match, $dir)) 
			return @unlink($dir);
		
		$result = true;
		
		if (file_exists($dir)):
			$dirPtr = @opendir($dir);
			if ($dirPtr):
			
				while ($file = @readdir($dirPtr)):
					if ($file == '.' || $file == '..') continue;
			
					if (is_dir($dir.'/'.$file)):
						$result = $result & $this->unlink_recursive($dir.'/'.$file, $match, $includeDirs);
					else:
						if (preg_match($match, $file)):
							$result = $result & @unlink($dir.'/'.$file);
						endif;
					endif;
				endwhile;
				
			endif;
		endif;
		if ($includeDirs) return ($result & @rmdir($dir));
	}

	
	/**
	 * Create directories structure, when it necessary.
 	 * 
   	 * @param $dir directories from the root
   	 * @param $root root, this directory will be not created
	 * @return bool TRUE when success, otherwise FALSE
	 */
	public function create_dirs($dir, $root='/') {

		if (!mkdir($dir, 0775, true))
			return false;

		return true;
    	
 	}
 	
 	
 	/**
 	 * Function check file on existable, readable and writable.
 	 * @param string $file path to file
 	 * @param string $description function here retrieves description of state
 	 * @param string $mode
 	 * @return if allright retrieves TRUE and in description contains "Ready",
 	 * 		   retrieves FALSE and in description contains comment.
 	 */
 	public function get_file_state($file, &$description, $mod='erw') {

 		$mod = strtolower($mod);
 		
 		/* check existable */
 		if (strpos($mod, 'e')!==false) {
 			if (!file_exists($file)) {
 				$description = 'Not found';
 				return false;
 			}
 		}
 		
 		/* check readable */
 		if (strpos($mod, 'r')!==false) {
 			if (!is_readable($file)) {
 				$description = 'Not readable';
 				return false;
 			}
 		}
 		
 		/* check writeable */
 		if (strpos($mod, 'w')!==false) {
 			if (!is_writeable($file)) {
 				$description = 'Not writeable';
 				return false;
 			}
 		}
 		
 		$description = 'Ready';
 		return true;
 		
 	}

 	
 	/**
 	 * Function formats size of file.
 	 * @param int $size size of file in bytes
 	 * @param int $precision precision, number of decimal cifer
 	 * @param bool $unit show unit after value (B, kB, MB, GB)
 	 * @param string $triple delimiter triple numbers
 	 * @param string $decimal decimal delimiter
 	 * @return string formatted size
 	 */
	function format_file_size($size, $precision=2, $unit=true, $triple=',', $decimal='.') {
 		
 		$sizeText = '';
		
 		// division value by size
		if (!$size) {
    		$sizeText = '0'. ($unit ? '&nbsp;B' : '');
    	} else {
    				
    		if ($size < 1024)
    			$sizeText = $size . ($unit ? '&nbsp;B' : '');
    		else if ($size < 1048576)
    			$sizeText = round($size/(float)1024, $precision) . ($unit ? '&nbsp;kB' : '');
    		else if ($size < 1073741824)
    			$sizeText = round($size/(float)1048576, $precision) . ($unit ? '&nbsp;MB' : '');
    		else
    			$sizeText = round($size/(float)1073741824, $precision) . ($unit ? '&nbsp;GB' : '');
    	}
    	
    	// for this settings don't process more
    	if ($triple='' && $decimal='.')
    		return (string)$sizeText;

    	// processing decimal and tripple delimiters
		$res  = '';
		$len  = strlen((string)$sizeText);
		$trip = 0;
		$dec  = false;
		for ($i=$len-1; $i>=0; $i--)	{
			if (!$dec && $sizeText[$i]!='.') {
				$res = $sizeText[$i].$res;
			} else if (!$dec && $sizeText[$i]=='.') {
				$dec = true;
				$res = $decimal.$res;
			} else {
				$res = $sizeText[$i].$res;
				$trip++;
				if ($trip==3 && $i>0) {
					$res  = $triple.$res;
					$trip = 0;				
				}
			}
		}
		
		// return result
    	return $res;
 		
 	}
	

}
