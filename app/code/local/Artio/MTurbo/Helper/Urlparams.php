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
 * Helper contains function for handling of url params.
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
class Artio_MTurbo_Helper_Urlparams extends Mage_Core_Helper_Abstract
{
    /* QUERY PARAMS USED IN M-TURBO EXTENSION */
    
    const NOCACHE       = 'nocache';                // it says "I want not cached page!"
    const DYNAMIC_BLOCK = 'mturbo_dynamic_block';   // it says "I want to use dynamic blocks!"

    /**
     * Clean additional query params from url.
     */
	function cleanQueryParams()
	{
	   $this->moveAndUnset(self::NOCACHE);
	   $this->moveAndUnset(self::DYNAMIC_BLOCK);
	}
	
	/**
	 * Move parameter from query string to Magento registers and then
	 * unset from query string.
	 * @param string $key
	 */
	function moveAndUnset($key)
	{
	    if (isset($_GET[$key]))
	    {
	        Mage::register($key, $_GET[$key], true);
	        unset($_GET[$key]);
	    }
	}
	
	/**
	 * Unset param from query string
	 * @param unknown_type $key
	 */
	function unsetParam($key)
	{
	    if (isset($_GET[$key]))
	    {
	        unset($_GET[$key]);
	    }
	}
	
	/**
	 * Retrieve param from query string, if there is none, look into Magento registers.
	 * @param string $key
	 * @return mixed
	 */
	function getParam($key)
	{
	   return isset($_GET[$key]) ? $_GET[$key] : Mage::registry($key);
	}
	
	/**
	 * Determine whether no cache parameter is in current url.
	 * @return bool TRUE if yes, otherwise FALSE
	 */
	function isNoCache()
	{
	    return (bool) $this->getParam('nocache');
	}
	

}