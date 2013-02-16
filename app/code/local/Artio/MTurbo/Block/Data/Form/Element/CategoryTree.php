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
 * Form button element
 * ABY TOTO FUNGOVALO MUSI BYT NAHRANE JAVASCRIPTY A AKCE OBSLUHUJICI AJAX viz. Adminhtml/CatalogController.php
 * funkce
 * categoriesJsonAction() 
 * _initCategory()
 * a vlozeni javascriptu 
 * $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
            ->setContainerCssClass('catalog-categories');
 *
 * @category   Artio
 * @package    Artio_MTurbo
 * @author     Artio Magento Team <info@artio.net>
 */
class Artio_MTurbo_Block_Data_Form_Element_CategoryTree extends Varien_Data_Form_Element_Abstract
{
	
	private $treeId='category_ids';
	private $categoryIds = array(0);
	private $updateElementId='update_element';
	private $formName='form_name';
	
	public function __construct($attributes=array()) {
		parent::__construct($attributes);
		$this->loadAttributesToData($attributes);
	}
	
	private function loadAttributesToData($attributes=array()) {
		foreach ($attributes as $key=>$value) {
			if (!empty($this->$key)) $this->$key = $value; 
		}
	}
		
	/**
	 * @see Varien_Data_Form_Element_Abstract::getHtml()
	 *
	 * @return unknown
	 */
	public function getHtml() {
		
		$categoryTree = Mage::getSingleton('core/layout')
			->createBlock('adminhtml/catalog_category_checkboxes_tree', $this->treeId,
                        array('js_form_object' => $this->getFormNameAsElement()));
                        
        $categoryTree->setTemplate('mturbo/preview/tree.phtml');
        $categoryTree->setData('updateElementId', $this->updateElementId);

        if (is_array($this->categoryIds)) 
        	$categoryTree->setCategoryIds($this->categoryIds);

		$categoryTreeHtml = $categoryTree->toHtml();
                			
        $html = $categoryTreeHtml;
        return $html;
	}
	
	private function getFormNameAsElement() {
		return "$('" . $this->formName . "')";
	}

    
}                           
