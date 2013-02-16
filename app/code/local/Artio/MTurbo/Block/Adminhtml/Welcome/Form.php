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
 * Installation form. Showed at first execute MTurbo Management.
 *
 * @category   Artio
 * @package    Artio_MTurbo
 * @author     Artio <info@artio.net>
 */
class Artio_MTurbo_Block_Adminhtml_Welcome_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm() {

      $form = new Varien_Data_Form(array(
        'name'=>'welcome_form', 
        'id' => 'welcome_form', 
        'action' =>  Mage::helper('adminhtml')->getUrl('*/*/install'),
        'method' => 'post'));
      
        $form->setUseContainer(true);
      
        $layoutFieldset = $form->addFieldset('general_fieldset', array(
            'legend' => Mage::helper('mturbo')->__('Your first options'),
            'class'  => 'fieldset',
        ));
        
        $layoutFieldset->addField('turbopath', 'text', array(
            'name'      => 'turbopath',
            'label'     => Mage::helper('mturbo')->__('Cache Path').':',
          	'value'   => 'var'.DS.'turbocache'
        ));
        
         /* for every website add one fieldset */
        $websiteCollection = Mage::getModel('core/website')->getCollection()->load();
        foreach ($websiteCollection as $website) {
          if ($website->getDefaultStore()) {
              $this->_addWebsiteFieldset($website, $form);
              break;
          }
        }
        
        $form->addType('widget_button', Artio_MTurbo_Helper_Data::FORM_WIDGET_BUTTON);
        $form->addField('install_button', 'widget_button', array(
          'name'    => 'install_button',
          'label'   => Mage::helper('mturbo')->__('Save and Install'),
          'onclick' => "welcome_form.submit()",
          'style'   => "text-align:right;"
        ));
        
        $this->setForm($form);
        
        return parent::_prepareForm();
    }
    
    /**
     * Method add website fieldset to the form.
     * @param Mage_Core_Model_Website $website
     */
    private function _addWebsiteFieldset($website, $form) {
      
      $prefixWeb = 'website-'.$website->getCode();
      
      /* make fieldset */
      $layoutFieldset = $form->addFieldset($prefixWeb.'_fieldset', array(
            'legend' => Mage::helper('mturbo')->__($website->getName() . ' settings'),
            'class'  => 'fieldset'));
      
      /* add extra user control */
      $layoutFieldset->addType('html_element', Artio_MTurbo_Helper_Data::FORM_HTML);
      $layoutFieldset->addType('widget_button', Artio_MTurbo_Helper_Data::FORM_WIDGET_BUTTON);
      
      /* indicator whether website is enabled */
        $layoutFieldset->addField($prefixWeb.'-enabled', 'select', array(
            'name'      => $prefixWeb.'-enabled',
            'label'     => Mage::helper('mturbo')->__('Enable website').':',
          'value'   => '1',
          'options' => array(
                  0 => Mage::helper('mturbo')->__('No'),
                  1 => Mage::helper('mturbo')->__ ( 'Yes' ))));
      
      /* add field for turbopath */
        $layoutFieldset->addField($prefixWeb.'-base_dir', 'text', array(
            'name'      => $prefixWeb.'-base_dir',
            'value'     => Mage::getBaseDir(),
            'label'     => Mage::helper('mturbo')->__('Base directory').':'));

        /* add field for server name */
        $layoutFieldset->addField($prefixWeb.'-server_name', 'text', array(
            'name'      => $prefixWeb.'-server_name',
            'value'     => Mage::helper('mturbo/website')->getServerName($website->getDefaultStore()->getCode()),
            'label'     => Mage::helper('mturbo')->__('Server name').':'));
        
        /* every store has one select determines whether enabled is */
        foreach ($website->getStores() as $store)
          if ($store->getIsActive())
            $layoutFieldset->addField($prefixWeb.'-store-'.$store->getCode(), 'select', array(
              'name'    => $prefixWeb.'-store-'.$store->getCode(),
              'label'   => $store->getGroup()->getName().'<br />'.$store->getName(),
              'value'   => '1',
              'options' => array(
                      0 => Mage::helper('mturbo')->__('No'),
                      1 => Mage::helper('mturbo')->__ ( 'Yes' ))));
                      
      
  
    } 
    
    protected function _afterToHtml($html) {
      return $this->_getOkText() . $html;
    }
    
    private function _getOkText() {
      $text = Mage::helper('mturbo')->__('Welcome text demo');
      return $this->_wrapInfoDiv($text);
    }
    
    private function _wrapErrorDiv($error) {
      return '<div style="margin-bottom:10px;padding:10px;background:#E06060;border:1px solid #802020">'.$error.'</div>';
    }
    
    private function _wrapInfoDiv($text) {
      return '<div style="margin-bottom:10px;padding:10px;">'.$text.'</div>';
    }
    
    
}