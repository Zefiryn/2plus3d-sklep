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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @copyright   Copyright (c) 2010 Artio (http://www.artio.net)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */

	$prefix = Mage::app()->getConfig()->getTablePrefix();

	$artioUrl = 'http://www.artio.net/magento-extensions/m-turbo-accelerator';

	$notification = Mage::getModel('adminNotification/inbox');
	$notf = array( 'is_read'=>0, 'date_added'=>date('Y-m-d H:i:s'), 'severity'=>4, 'url'=>$artioUrl );

	try {

		$installer = $this;

		$installer->startSetup();

		$installer->run("

		DELETE FROM `".$prefix."adminnotification_inbox` WHERE `url`= '".$artioUrl."';

		TRUNCATE TABLE `".$prefix."mturbo`;
		
		ALTER TABLE `".$prefix."mturbo` 
			CHANGE `request_path` `request_path` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

        ALTER TABLE `".$prefix."mturbo` ADD UNIQUE `REQUEST_PATH_UNIQUE` ( `request_path`, `store_id` ) 
		
		");
		
		$notf['title'] 			= Mage::helper('mturbo')->__('M-Turbo upgrade succesfull. Please see into System/M-Turbo Management');
		$notf['description']	= Mage::helper('mturbo')->__("Upgrade succesfull.");
		
		$notification->parse(array($notf));

		$installer->endSetup();

	} catch (Exception $e) {
		
		$notf['title'] 			= Mage::helper('mturbo')->__('M-Turbo upgrading problem');
		$notf['description']	= $e->getMessage();
		$notification->parse(array($notf));
		
	}
