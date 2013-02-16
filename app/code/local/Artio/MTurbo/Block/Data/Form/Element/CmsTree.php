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
 * @category   Artio	
 * @package    Artio_MTurbo
 * @copyright  Copyright (c) 2010 Artio (http://www.artio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tree for cms pages
 *
 * @category   Artio
 * @package    Artio_MTurbo
 * @author     Artio Magento Team <info@artio.net>
 */
class Artio_MTurbo_Block_Data_Form_Element_CmsTree extends Varien_Data_Form_Element_Abstract
{
	
	public function __construct($attributes=array()) {
		parent::__construct($attributes);
	}
	
	private function _prepareStoresCollection() {
		return Mage::getModel('core/store')->getCollection()->addFilter('is_active', 1)->load()->getItems();
	}
	
	private function _prepareCMSCollection() {
		
		/* load cms pages */
		$cmsCollection 	= Mage::getModel('cms/page')->getCollection()
							->addFilter('is_active', 1)
							->load();
			
		/* gather ids of load pages */
		$items = $cmsCollection->getColumnValues('page_id');	
			
		/* select page => store */
        $resource = Mage::getResourceModel('cms/page_collection');
        $select	  =	$resource->getConnection()
        						->select()
                  				->from($resource->getTable('cms/page_store'))
                  				->where($resource->getTable('cms/page_store').'.page_id IN (?)', $items);
                  			
        /* transform store to associative array page_id=>store_id */
        $arrayKey = array();
        if ($result = $resource->getConnection()->fetchAll($select))
        	foreach ($result as $row)
        		if (!isset($arrayKey[$row['page_id']]))
        			$arrayKey[$row['page_id']] = array($row['store_id']);
        		else
        			$arrayKey[$row['page_id']][] = $row['store_id'];
        
        /* assign store id array to page */
        foreach ($cmsCollection->getItems() as $cms)
        	$cms->setData('store_id', $arrayKey[$cms->getPageId()]);
        			
        return $cmsCollection->getItems();
		
	}
		
	/**
	 * @see Varien_Data_Form_Element_Abstract::getHtml()
	 *
	 * @return unknown
	 */
	public function getHtml() {
				
		/* get helper */
		$helper = Mage::helper('mturbo');
		
		/* get config */
		$config = Mage::getSingleton('mturbo/config');
		
		/* get collections */
		$pages  = $this->_prepareCMSCollection();
		$stores = $this->_prepareStoresCollection();
		
		/* get array from configuration */
		$pWith 		= $this->getWith();
		$pWithout 	= $this->getWithout();
		
		/* get array of ids */
		$storesIds = array();
		foreach ($stores as $store) $storesIds[] = $store->getId();
		
		/* generating html */
		$html  = '';
		$html .= '<div id="cms_tree">';
		foreach ($pages as $cms) {

			if ($cms->getIdentifier()=='no-route') continue;
			if ($cms->getIdentifier()!='home') continue;

			$name 	= "cms_tree_".$cms->getId();
			$check	= (in_array($cms->getId(), $pWithout) ? 'checked="checked"' : '');
			$block	= (in_array($cms->getId(), $pWithout) ? 'block' : 'none');
			
			$html .= '<div class="x-tree-lines x-tree-node-collapsed" style="margin-left:0px" id="cms_tree_'.$cms->getId().'">';
			$html .= '<span class="x-tree-node-icon">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>';
			$html .= '<input onclick="clickCmsTree(this);" type="checkbox" id="'.$name.'" name="'.$name.'" '.$check.'/>';
			$html .= '<span style="margin-left:10px">'.$cms->getTitle().'</span>';
			
				$html .= '<div style="margin-left:16px;display:'.$block.'" id="'.$name.'_block">';
				$storeArray = $cms->getStoreId();
		        if (!is_array($storeArray))
                {
                   $storeArray = array($storeArray);
                }
				foreach ($stores as $store) {
					
					$storeName 	= $name.'_'.$store->getId();
					$storeCheck	= (in_array($cms->getId().'_'.$store->getId(), $pWith) ? 'checked="checked"' : '');
					
					$website = $config->getWebsiteConfigByStoreviewCode($store->getCode());
					$enabled = isset($website) && ($website->isStoreViewEnabled($store->getCode()));

					if ((in_array(0, $storeArray) || in_array($store->getId(), $storeArray)) && $enabled) {
						$html .= '<span class="x-tree-elbow">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>';
						$html .= '<input type="checkbox" id="cms_tree_'.$cms->getId().'_'.$store->getId().'" name="'.$storeName.'" '.$storeCheck.' />';
						$html .= '<span style="margin-left:10px">'.$cms->getTitle().' ('.$store->getName().')</span>';
						$html .= '<br />';
					}
					
				}
				$html .= '</div>';
			
			$html .= '</div>';
		}
		$html .= '</div>';
		
		/* generating javascript */
		$html .= '<script type="text/javascript">'."\n";
		$html .= '//<![CDATA['."\n";
		
		$html .=    'var storeIds = ['.implode(',', $storesIds).'];
					 function clickCmsTree(el) {
						if (el) {
							var elBlock = document.getElementById(el.id+"_block");

							if (elBlock) {
							
								var display;
								var checked;
								if (elBlock.style.display=="none") {
									display = "block";
									checked = "checked";
								} else {
									display = "none";
									checked = "";
								}

								for(var i=0;i<storeIds.length;i++){
									var elChild = document.getElementById(el.id+"_"+storeIds[i]);
									if (elChild) {
										elChild.checked = checked;	
									}
								}
								elBlock.style.display = display;
							}
						}
					}';
		
		$html .= '//]]>'."\n";
		$html .= '</script>';
		
		return $html;
	}

    
}                           
