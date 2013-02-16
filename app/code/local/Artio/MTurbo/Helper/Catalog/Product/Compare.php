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
 * @copyright   Copyright (c) 2011 Artio (http://www.artio.net)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog Product Compare Helper
 *
 * @category   Artio
 * @package    Artio_Catalog
 * @author     Artio Magento Team <info@artio.net>
 */
class Artio_MTurbo_Helper_Catalog_Product_Compare extends  Mage_Catalog_Helper_Product_Compare
{

		public function getMTurboUrl() {
				$referer = Mage::registry('mturbo_referer');
				$url = (isset($referer) && is_string($referer)) ? $referer : Mage::helper('core/url')->getEncodedUrl();
		}
    
    /**
     * Retrieve compare list url
     *
     * @return string
     */
    public function getListUrl()
    {
         $itemIds = array();
         foreach ($this->getItemCollection() as $item) {
             $itemIds[] = $item->getId();
         }

         $params = array(
            'items'=>implode(',', $itemIds),
            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl()
         );

         return $this->_getUrl('catalog/product_compare', $params);
    }

    /**
     * Retrieve remove item from compare list url
     *
     * @param   $item
     * @return  string
     */
    public function getRemoveUrl($item)
    {
        $params = array(
            'product'=>$item->getId(),
            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->getMTurboUrl()
        );
        return $this->_getUrl('catalog/product_compare/remove', $params);
    }

    /**
     * Retrieve clear compare list url
     *
     * @return string
     */
    public function getClearListUrl()
    {
        $params = array(
            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->getMTurboUrl()
        );
        return $this->_getUrl('catalog/product_compare/clear', $params);
    }

}
