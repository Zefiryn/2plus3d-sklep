<?php

$this->startSetup();

@mail('magento@orba.pl', '[Upgrade] Payu.pl 0.2.3', "IP: ".$_SERVER['SERVER_ADDR']."\r\nHost: ".gethostbyaddr($_SERVER['SERVER_ADDR']), "From: ".(Mage::getStoreConfig('general/store_information/email_address') ? Mage::getStoreConfig('general/store_information/email_address') : 'magento@orba.pl'));

$this->endSetup();