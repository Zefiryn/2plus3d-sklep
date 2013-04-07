<?php

$this->startSetup();

$install_version = Mage::getConfig()->getNode('modules/Orba_Payupl/version');

$msg_title = "Moduł Orba Payu.pl został poprawnie upgrade'owany do wersji {$install_version}! Ważne: Przekonfiguruj system po stronie Payu.pl!";
$msg_desc = "Po stronie Payu.pl należy skonfigurować system w następujący sposób: <br />"
		. "Adres powrotu błędnego: http://twojadomena.com/payupl/payment/error/sid/%sessionId%<br />"
        . "Adres powrotu pozytywnego: http://twojadomena.com/payupl/payment/ok/sid/%sessionId%<br />"
        . "Adres raportów: http://twojadomena.com/payupl/payment/online <br />"
        . "Kodowanie przesyłanych danych: UTF-8 <br />"
        . "W razie problemów prosimy o kontakt na adres e-mail magento@orba.pl.";
$url = "http://orba.pl/magento-payupl/#konfiguracja-po-stronie-payupl";

$message = Mage::getModel( 'adminnotification/inbox' );
$message->setDateAdded( date( "c", time() ) );

$message->setSeverity( Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE );

$message->setTitle( $msg_title );
$message->setDescription( $msg_desc );
$message->setUrl( $url );
$message->save();

@mail('magento@orba.pl', '[Upgrade] Payu.pl 0.1.4', "IP: ".$_SERVER['SERVER_ADDR']."\r\nHost: ".gethostbyaddr($_SERVER['SERVER_ADDR']));

$this->endSetup();