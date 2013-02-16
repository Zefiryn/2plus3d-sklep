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
 * @copyright   Copyright (c) 2011 Artio (http://www.artio.net)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping cart item render block
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team <info@artio.net>
 */
class Artio_MTurbo_Block_Checkout_Cart_Item_Renderer extends Mage_Checkout_Block_Cart_Item_Renderer
{

    /**
     * Get item delete url
     *
     * @return string
     */
    public function getDeleteUrl()
    {
				$referer = Mage::registry('mturbo_referer');
				$url = (isset($referer) && is_string($referer)) ? $referer : Mage::helper('core/url')->getEncodedUrl();
        return $this->getUrl(
            'checkout/cart/delete',
            array(
                'id'=>$this->getItem()->getId(),
                Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $url
            )
        );
    }

}
