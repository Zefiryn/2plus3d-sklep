<?php

class Orba_Payupl_Helper_Data extends Mage_Core_Helper_Abstract {

    public function sendPost($url, $args = array()) {
        return $this->sendRequest($url, $args, Zend_Http_Client::POST);
    }
    
    public function sendRequest($url, $args = array(), $method = Zend_Http_Client::GET) {
        $client = new Zend_Http_Client($url);
        $adapter = new Zend_Http_Client_Adapter_Curl();
        $adapter->setConfig(array(
            'curloptions' => array(
                CURLOPT_SSLVERSION => 1,
                // our experience shows that Payu doesn't need this option
                // older versions of CURL don't understand it
                // hence it's commented
                // 
                // CURLOPT_SSL_CIPHER_LIST => 'TLSv1',
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_SSL_VERIFYPEER => false
            )
        ));
        $client->setAdapter($adapter);
        if (!empty($args)) {
            foreach ($args as $name => $value) {
                $client->setParameterPost($name, $value);
            }
        }
        try{
            $response = $client->request($method);
            if ($response->isSuccessful()) {
                $headers = $response->getHeaders();
                $gzipped = isset($headers['Content-encoding']) && $headers['Content-encoding'] === 'gzip';
                return $gzipped ? $this->decodeGzip($response->getRawBody()) : $response->getRawBody();
            } else {
                Mage::log('Unable to send request to Payu: '.$response->getStatus(), null, 'payupl.log');
            }
        } catch (Exception $e) {
            Mage::log('Unable to send request to Payu: '.$e->getMessage(), null, 'payupl.log');
        }
        return false;
    }

    public function decodeGzip($data) {
        return Zend_Http_Response::decodeGzip($data);
    }

}
