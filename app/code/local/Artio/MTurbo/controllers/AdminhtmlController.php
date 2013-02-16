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
 * Controller
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
class Artio_MTurbo_AdminhtmlController extends Mage_Adminhtml_Controller_Action
{


	/**
   * Execute when user saves configuration. Button at right top corner.
   */
	public function saveAction() {
				
		try {
	
			$request = $this->getRequest();

			$config = Mage::getSingleton('mturbo/config');
			/* extract post data for websites and cms and dynamic blocks configuration */
			Mage::getSingleton('mturbo/config_websiteTransformer')->extractData($config, $request->getPost());
			Mage::getSingleton('mturbo/config_cmsTransformer')->extractData($config, $request->getPost());
			Mage::getSingleton('mturbo/config_dynamicTransformer')->extractData($config, $request->getPost());
			$config->save($request->getPost());

			$this->_getSession()->addSuccess(Mage::helper('mturbo')->__('Configuration was successfully saved'));

		} catch (Exception  $e) {

			$this->_getSession()->addError(Mage::helper('mturbo')->__('Configuration error').' : '.$e->getMessage());

		}
		
		/* redirect to index page */
		$this->_redirect('mturbo/adminhtml_mturbo/index', array('activeTab'=>$request->getPost('activeTab')));

	}
	
}
