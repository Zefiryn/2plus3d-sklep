<?php

class DpTD_Catalog_Block_Cart extends Mage_Catalog_Block_Product_View {

  protected function _construct() {
    $this->addData(array(
        'cache_lifetime' => 36600,
        'cache_tags' => array(Mage_Catalog_Model_Product::CACHE_TAG . "_" . $this->getProduct()->getId()),
        'cache_key' => $this->getProduct()->getId(),
    ));
  }

}
