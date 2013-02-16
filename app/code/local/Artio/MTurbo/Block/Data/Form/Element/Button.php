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
 * @category   Varien
 * @package    Varien_Data
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Form button element
 *
 * @category   Artio
 * @package    Artio_MTurbo
 * @author     Artio Magento Team <info@artio.net>
 */
class Artio_MTurbo_Block_Data_Form_Element_Button extends Varien_Data_Form_Element_Abstract
{
	
	private $label;
	private $onclick;
	private $comment;
	private $style;
	
    public function __construct($attributes=array()) 
    {
        parent::__construct($attributes);
        if (array_key_exists('label', $attributes)) $this->label = $attributes['label'];
        if (array_key_exists('onclick', $attributes)) $this->onclick = $attributes['onclick'];
        if (array_key_exists('comment', $attributes)) $this->comment = $attributes['comment'];
        if (array_key_exists('style', $attributes)) $this->style = $attributes['style'];
    }
	
	/**
	 * @see Varien_Data_Form_Element_Abstract::getHtml()
	 *
	 * @return unknown
	 */
	public function getHtml() {

		$html  = '<tr><td colspan="2">';
		$html .= '<div style="margin-bottom:10px;'.$this->style.'">';
		$html .= Mage::getSingleton('core/layout')
                		->createBlock('adminhtml/widget_button', '', array(
                    		'label'   => $this->label,
                    		'type'    => 'button',
                    		'onclick' => $this->onclick,
                			))->toHtml();
        $html .= '<div style="margin-top:5px;width:50%"><i>'.$this->comment.'</i></div>';
        $html .= '</div>';
        $html .= '</td></tr>';  
        $html .= $this->getAfterElementHtml();   			
        return $html;
	}

    
}                           
