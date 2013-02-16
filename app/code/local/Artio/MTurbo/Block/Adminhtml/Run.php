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
 * @category    Artio
 * @package     Artio_MTurbo
 * @copyright   Copyright (c) 2010 Artio (http://www.artio.net)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Indicator downloading pages.
 *
 * @category   Artio
 * @package    Artio_MTurbo
 * @author     Artio Magento Team <info@artio.net>
 */
class Artio_MTurbo_Block_Adminhtml_Run extends Mage_Adminhtml_Block_Abstract
{
	
	private $_importIds;
	
	public function setImportIds($importIds) {
		$this->_importIds = (is_array($importIds)) ? $importIds : explode(",", $importIds);
		return $this;
	}
	
	public function isSetImportIds() {
		return (!empty($this->_importIds));
	}
	
	public function getImportIds() {
		return $this->_importIds;
	}
	
	protected function _toHtml() {
		
		//$profile = $this->getProfile();
		
		echo '<html>';
			
			echo '<head>';
       			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        		echo '<script type="text/javascript">var FORM_KEY = "'.Mage::getSingleton('core/session')->getFormKey().'";</script>';
				echo $this->_getCssJsHtml();
    			echo '<title>'.Mage::helper('mturbo')->__('M-Turbo Management - Downloading pages').'</title>';
			echo '</head>';
			
			echo '<body>';
			
			
			
				echo '<ul>';
        			echo '<li>';
            			echo '<img src="'.Mage::getDesign()->getSkinUrl('images/note_msg_icon.gif').'" class="v-middle" style="margin-right:5px"/>';
            			echo $this->__("Starting download pages, please wait...");
            		echo '</li>';
            		echo '<li style="background-color:#FFD;">';
            			echo '<img src="'.Mage::getDesign()->getSkinUrl('images/fam_bullet_error.gif').'" class="v-middle" style="margin-right:5px"/>';
            			echo $this->__("Warning: Please don't close window during downloading pages");
        			echo '</li>';
        		echo '</ul>';
        		
          		echo '<ul>'; 		
            		echo '<li id="liFinished" style="display:none;">';
            			echo '<img src="'.Mage::getDesign()->getSkinUrl('images/note_msg_icon.gif').'" class="v-middle" style="margin-right:5px"/>';
            			echo $this->__("Finished downloading.");
            		echo '</li>';
            		
            	echo '</ul>';
            	
            	$showFinished = true;
            	
            	$importIds = ($this->isSetImportIds()) ?
            		$this->getImportIds() :
            		Mage::getModel('mturbo/mturbo')->getCollection()->getAllIds();

            	if (count($importIds)>0) {

                    	$showFinished = false;
                    	$countItems = count($importIds);

                    	$batchConfig = array(
                        	'styles' => array(
                            	'error' => array(
                                	'icon' => Mage::getDesign()->getSkinUrl('images/error_msg_icon.gif'),
                                	'bg'   => '#FDD'),
                            	 'message' => array(
                                	'icon' => Mage::getDesign()->getSkinUrl('images/fam_bullet_success.gif'),
                                	'bg'   => '#DDF'),
                            	 'loader'  => Mage::getDesign()->getSkinUrl('images/ajax-loader.gif')),
                        	'template' => '<li style="#{style}" id="#{id}">'
                                    . '<img id="#{id}_img" src="#{image}" class="v-middle" style="margin-right:5px"/>'
                                    . '<span id="#{id}_status" class="text">#{text}</span>'
                                    . '</li>',
                        	'text'     => $this->__('Processed <strong>%s%% %s/%d</strong> pages', '#{percent}', '#{updated}', $countItems),
                        	'successText'  => $this->__('Downloaded <strong>%s</strong> pages', '#{updated}')
                    	);
        		
			
						echo $this->_getAjaxScript($batchConfig, $countItems);
				
			
	                  	foreach ($importIds as $id) {
                			$data = array( 'batch_id'   => $id, 'rows[]' => $id );
                    		echo '<script type="text/javascript">addImportData('.Zend_Json::encode($data).')</script>';
                 		}
                 
                  		echo '<script type="text/javascript">execImportData()</script>';
 
            	}

            	if ($showFinished) {
                	echo "<script type=\"text/javascript\">$('liFinished').show();</script>";
            	}
            				
        	echo '</body>';
		echo '<html>';
		
	}
	
	private function _getCssJsHtml() {
		
		$headBlock = $this->getLayout()->createBlock('page/html_head');
        $headBlock->addJs('prototype/prototype.js');
        $headBlock->addJs('mage/adminhtml/loader.js');
        echo $headBlock->getCssJsHtml() .  $this->_getMyCssHtml();
		
	}
	
	private function _getMyCssHtml() {
		return '<style type="text/css">
    				ul { list-style-type:none; padding:0; margin:0; }
    				li { margin-left:0; border:1px solid #ccc; margin:2px; padding:2px 2px 2px 2px; font:normal 12px sans-serif; }
    				img { margin-right:5px; }
    	 		</style>';
	}
	
	private function _getAjaxScript($batchConfig, $countItems) {
	return '

<script type="text/javascript">
	var countOfStartedProfiles = 0;
	var countOfUpdated = 0;
	var countOfError = 0;
	var importData = [];
	var totalRecords = ' . $countItems . ';
	var config= '.Zend_Json::encode($batchConfig).';
</script>
				
<script type="text/javascript">
					
	function addImportData(data) {
    	importData.push(data);
	}

	function execImportData() {
					
    	if (importData.length == 0) {
        	$("updatedRows_img").src = config.styles.message.icon;
        	$("updatedRows").style.backgroundColor = config.styles.message.bg;
        	Element.insert($("liFinished"), {before: config.tpl.evaluate({
            	style: "background-color:"+config.styles.message.bg,
            	image: config.styles.message.icon,
            	text: config.tplSccTxt.evaluate({updated:(countOfUpdated-countOfError)}),
            	id: "updatedFinish"
        	})});

        	if ($("liBeforeFinish")) {
            	Element.insert($("liFinished"), {before: $("liBeforeFinish")});
            	$("liBeforeFinish").show();
        	}

        	new Ajax.Request("' .$this->_getBatchFinishUrl().'", {
            	method: "post",
            	parameters: {form_key: FORM_KEY},
            			
            	onComplete: function(transport) {
                	if (transport.responseText.isJSON()) {
                    	var response = transport.responseText.evalJSON();
                    	if (response.error) {
                        	Element.insert($("liFinished"), {before: config.tpl.evaluate({
                            	style: "background-color:"+config.styles.error.bg,
                            	image: config.styles.error.icon,
                            	text: response.error.escapeHTML(),
                            	id: "error-finish"
                       		 })});
                    	}
                	}

                	if ($("before-finish-wait-img")) {
                    	$("before-finish-wait-img").hide();
                	}

                	$(\'liFinished\').show();
            	}
        	});
        	
    	} else {
        	sendImportData(importData.shift());
    	}
	}

	function sendImportData(data) {
	
    	if (!config.tpl) {
        	config.tpl = new Template(config.template);
        	config.tplTxt = new Template(config.text);
        	config.tplSccTxt = new Template(config.successText);
    	}
    	
    	if (!$("updatedRows")) {
        	Element.insert($("liFinished"), {before: config.tpl.evaluate({
            	style: "background-color: #FFD;",
            	image: config.styles.loader,
            	text: config.tplTxt.evaluate({updated:countOfUpdated, percent:getPercent()}),
            	id: "updatedRows"
        	})});
    	}
    	
    	countOfStartedProfiles++;
    	if (!data.form_key) {
        	data.form_key = FORM_KEY;
    	}

   		new Ajax.Request("'.$this->_getBatchRunUrl().'", {
      		method: "post",
      		parameters: data,
      		onSuccess: function(transport) {
        		countOfStartedProfiles --;
        		countOfUpdated ++;
        		if (transport.responseText.isJSON()) {
            		addProfileRow(transport.responseText.evalJSON());
        		} else {
           			Element.insert($("updatedRows"), {before: config.tpl.evaluate({
                		style: "background-color:"+config.styles.error.bg,
                		image: config.styles.error.icon,
                		text: transport.responseText.escapeHTML(),
                		id: "error-" + countOfStartedProfiles
            		})});
            		countOfError ++;
        		}
        		execImportData();
      		}
    	});
	}

	function getPercent() {
    	return Math.ceil((countOfUpdated/totalRecords)*1000)/10;
	}

	function addProfileRow(data) {
    	if (data.errors.length > 0) {
        	for (var i=0, length=data.errors.length; i<length; i++) {
            	Element.insert($("updatedRows"), {before: config.tpl.evaluate({
                	style: "background-color:"+config.styles.error.bg,
                	image: config.styles.error.icon,
                	text: data.errors[i],
                	id: "id-" + (countOfUpdated + i + 1)
            	})});
            	countOfError ++;
        	}
        }
        if (data.messages.length > 0) {
        	for (var i=0, length=data.messages.length; i<length; i++) {
        		Element.insert($("updatedRows"), {before: config.tpl.evaluate({
                	style: "background-color:"+config.styles.message.bg,
                	image: config.styles.message.icon,
                	text: data.messages[i],
                	id: "id-" + (countOfUpdated + i + 1)
            	})});
        	}
    	}
    	$("updatedRows_status").update(config.tplTxt.evaluate({updated:countOfUpdated, percent:getPercent()}));
	}
</script>';
          
	}
	
	private function _getBatchFinishUrl() {
		return $this->getUrl('*/*/downloadFinish'/*, array('id' => $batchModel->getId())*/);
	}
	
	private function _getBatchRunUrl() {
		return $this->getUrl('*/*/downloadRun');
	}
	
}
