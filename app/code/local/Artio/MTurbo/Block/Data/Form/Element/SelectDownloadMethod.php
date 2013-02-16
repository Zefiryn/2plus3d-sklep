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
 * Select element with testing download method
 *
 * @category   Artio
 * @package    Artio_MTurbo
 * @author     Artio Magento Team <info@artio.net>
 */
class Artio_MTurbo_Block_Data_Form_Element_SelectDownloadMethod extends Varien_Data_Form_Element_Select
{

	public function getElementHtml()
    {
        $this->addClass('select');
        
        $html = self::getTestJS();
        
        $html .= '<select onchange="selectMethod('."'".$this->getHtmlId()."'".')"; id="'.$this->getHtmlId().'" name="'.$this->getName().'" '.$this->serialize($this->getHtmlAttributes()).'>'."\n";
        
        $value = $this->getValue();
        if (!is_array($value)) {
            $value = array($value);
        }

        $values = $this->getValues();
        if ($values) {
            foreach ($values as $key => $option) {
                if (!is_array($option)) {
                    $html.= $this->_optionToHtml(array(
                        'value' => $key,
                        'label' => $option),
                        $value
                    );
                }
                elseif (is_array($option['value'])) {
                    $html.='<optgroup label="'.$option['label'].'">'."\n";
                    foreach ($option['value'] as $groupItem) {
                        $html.= $this->_optionToHtml($groupItem, $value);
                    }
                    $html.='</optgroup>'."\n";
                }
                else {
                    $html.= $this->_optionToHtml($option, $value);
                }
            }
        }

        $html.= '</select>';
        
        // result test label
        $html.= '<div style="padding:10px 0px">';
        $html.= '<span><b>'.Mage::helper('mturbo')->__('Download Test').'</b></span><br />';
        $html.= '<span style="padding-left:15px" id="method'.$this->getHtmlId().'"><i>For test choose a method</i></span><br />';
        $html.= '<span style="padding-left:15px" id="testconnect'.$this->getHtmlId().'"></span>';
        $html.= '</div>';
        
        $html.= $this->getOnFlyJS();
        $html.= $this->getAfterElementHtml();
        return $html;
    }

    private function getOnFlyJS()
    {
    	$res  = '<script type="text/javascript">';
    	$res .= '  selectMethod("'.$this->getId().'")';
    	$res .= '</script>';
    	return $res;
    }
    
    static $jsinserted = false;
        
	private static function getTestJS()
	{
		

		if (!self::$jsinserted)
		{
			
			self::$jsinserted = true;
			$ajaxUrl = Mage::helper('adminhtml')->getUrl('*/*/testdownload');
					
			$res = '<script type="text/javascript">var FORM_KEY = "'.Mage::getSingleton('core/session')->getFormKey().'";</script>';
		
			$res .= '<script type="text/javascript">'."\n";
			
			$res .= "function selectMethod(id) {
			
						var el = document.getElementById(id);
						
						new Ajax.Request('$ajaxUrl', {
            				method: 'post',
            				parameters: {form_key: FORM_KEY, method: el.value},
            			
            				onComplete: function(transport) {
            				
                				if (transport.responseText.isJSON()) {
                				
                					var meth = document.getElementById('method'+id);
            						var sel	 = document.getElementById(id);
            						meth.innerHTML = sel.options[sel.selectedIndex].text;
                				
                					var el = document.getElementById('testconnect'+id);
                    				var response = transport.responseText.evalJSON();
                    				if (!response.ok) {
                    					el.style.color = 'red';
                    					el.innerHTML = 'FAIL (' + response.resultTest + ')';
									} else {
										el.style.color = 'green';
										el.innerHTML = 'OK (frontpage size: ' + response.resultTest + ' kB)';
									}                   
                       		 	} else {	
                       		 		var meth = document.getElementById('method'+id);
                       		 		meth.innerHTML = '<i>Test failed</i>';
                       		 		var el = document.getElementById('testconnect'+id);
                       		 		el.innerHTML = '';
                       		 		
								}
                       		}
                    	});
                    	
                    	var meth = document.getElementById('method'+id);
                       	meth.innerHTML = '<i>Testing...</i>';
                       	var el = document.getElementById('testconnect'+id);
                       	el.innerHTML = '';
                	
					 }";

			$res .= '</script>'."\n";
			
		}
		
		return $res;
	}
}                           
