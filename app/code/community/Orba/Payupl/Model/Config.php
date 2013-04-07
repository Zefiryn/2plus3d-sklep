<?php

class Orba_Payupl_Model_Config
{
    public function __construct($params = array())
    {
      
    }  
        
    public function getGatewayUrl()
    {
        return Mage::getStoreConfig('payment/payupl/gateway_url');
    }
    
    public function getPosId()
    {
        return Mage::getStoreConfig('payment/payupl/pos_id');
    }
    
    public function getPosAuthKey()
    {
        return Mage::getStoreConfig('payment/payupl/pos_auth_key');
    }
    
    public function getMD5Key1()
    {
        return Mage::getStoreConfig('payment/payupl/md5key1');
    }
    
    public function getMD5Key2()
    {
        return Mage::getStoreConfig('payment/payupl/md5key2');
    }
    
    public function getEncoding()
    {
        return Mage::getStoreConfig('payment/payupl/encoding');
    }
      
}
  