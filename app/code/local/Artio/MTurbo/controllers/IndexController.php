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
class Artio_MTurbo_IndexController extends Mage_Core_Controller_Front_Action
{


	/**
  	 * Router for receiving a request for dynamic blocks.
  	 */
	public function indexAction() {
		
		// prevent mturbo replacing
		Mage::register('mturbo_no_ajax', true, true);

		// save to session previous referer for product cart remove and compare remove
		$referer = $this->getRequest()->getParam('referer');
		Mage::register('mturbo_referer', $referer, true);
			
		// get block identifers
		$identifiers = $this->getRequest()->getParam('identifier');
		if (is_array($identifiers)) {
		
			// output is empty
			$output = '<blocks>';
			
			// load layout
			$this->loadLayout('default', false, true);
			$layout = $this->getLayout();
			
			// foreach identifier
			$dynamic = Mage::getSingleton('mturbo/config_dynamicTransformer');
			foreach ($identifiers as $identifier) {

				$node;
				$name;
				
				// default blocks are loaded by type
				// non-default blocks are loaded by name in layout
				if ($dynamic->isDefaultBlock($identifier)) {
					
					$type = $dynamic->getType($identifier);
					$node = $layout->getXpath("//block[@type='".$type."']/parent::*");
					$name = $layout->getXpath("//block[@type='".$type."']/@name");
					
				} else {
					
					$node = $layout->getXpath("//block[@name='".$identifier."']/parent::*");
					$name = $identifier;
					
				}

				// if $name is array, then must be $name prepared from array
				if (is_array($name) && isset($name[0])) {
					$temp = "";
					foreach ($name[0] as $k=>$v) {
						$temp = (string)$v;
						break;
					}
					$name = $temp;
				}

				// update for link text
				$output .= $this->_updateCartLinkText();
									
				// check node
				if (is_array($node) && count($node)>0) {

					try {
						
						$layout->generateBlocks($node[0]);
						$layout->addOutputBlock($name);
						
						// generate and catch output
						$output .= '<block name="'.$identifier.'">';
						$output .= $layout->getOutput();
						$output .= '</block>';
						
						$layout->removeOutputBlock($name);

					} catch (Exception $e) {}
						
				}
					
			}
			$output .= '</blocks>';
			echo $this->_filterAmpersand($output);
		}	
		// here is end script, because this action will be called by ajax only
		exit(0);
	}
	
	private function _updateCartLinkText() {
		$count = Mage::helper('checkout/cart')->getSummaryCount();
		if( $count == 1 ) {
        	$text = $this->__('My Cart (%s item)', $count);
        } elseif( $count > 0 ) {
          	$text = $this->__('My Cart (%s items)', $count);
        } else {
          	$text = $this->__('My Cart');
       	}
		return "<block name='cartlink'>".$text."</block>";
	}
	
	private function _filterAmpersand($text) {
	
		$size = strlen($text);

		$ntext = '';

		$pos = 0;
		while ($pos<$size) {

			$char   = $text[$pos];
			$ntext .= $char;
		
			if (($char=='&') && (($pos>=$size-4) || ($text[$pos+1]!='a') || ($text[$pos+2]!='m') || ($text[$pos+3]!='p') || ($text[$pos+4]!=';'))) {
				$ntext .= 'amp;MTURBO!';			
			}

			$pos++;

		}

		return $ntext;

	}
	
}