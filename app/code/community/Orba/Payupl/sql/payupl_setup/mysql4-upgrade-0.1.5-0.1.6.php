<?php

$this->startSetup();

@mail('magento@orba.pl', '[Upgrade] Payu.pl 0.1.6', "IP: ".$_SERVER['SERVER_ADDR']."\r\nHost: ".gethostbyaddr($_SERVER['SERVER_ADDR']));

$this->endSetup();