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
 * Grid tab.
 *
 * @category    Artio
 * @package     Artio_MTurbo
 * @author      Artio Magento Team (jiri.chmiel@artio.cz)
 */
class Artio_MTurbo_Block_Adminhtml_Edit_Tab_Url 
	extends Mage_Adminhtml_Block_Widget_Grid
	implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('url_section');
      $this->setDefaultSort('mturbo_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection() {
      
      $collection = Mage::getModel('mturbo/mturbo')->getCollection();
      $this->setCollection($collection);
  	  
      return parent::_prepareCollection();
      
  }
  
  protected function _afterLoadCollection() {
  	
  	foreach ($this->getCollection() as $model) {
      	$model->loadFileInformation();
    }
      
  }

  protected function _prepareColumns() {

      $this->addColumn('mturbo_id', array(
          'header'    => Mage::helper('mturbo')->__('ID'),
		  'width'	  => '15px',
          'align'     => 'center',
          'index'     => 'mturbo_id',
      ));
      
      $this->addColumn('store_id', array(
            'header'    => $this->__('Store View'),
            'index'     => 'store_id',
            'type'      => 'store',
            'store_view' => true,
        ));

      $this->addColumn('url', array(
          'header'    => Mage::helper('mturbo')->__('Request path'),
          'align'     =>'left',
          'index'     => 'request_path',
      ));
      
      $this->addColumn('type', array(
           'header'	  => Mage::helper('mturbo')->__('Type'),
      	   'align'	  => 'left',
      	   'type' 	  => 'options',
           'index'    => 'type',
           'options'   => array(
      		  'category' => Mage::helper('mturbo')->__('Category view'),
              'product'  => Mage::helper('mturbo')->__('Product detail'),
      		  'cms'		 => Mage::helper('mturbo')->__('CMS page'),
      		  'unknow'	 => Mage::helper('mturbo')->__('Other')
           )
      ));
      
      $this->addColumn('exist', array(
          'header'    => Mage::helper('mturbo')->__('Cached'),
          'align'     => 'center',
      	  'type' 	  => 'select',
          'index'     => 'exist',
          'filter'    => false,
          'sortable'  => false,
      	  'renderer'  => new Artio_MTurbo_Block_Data_Grid_Column_ColorOption(array(0=>'red',1=>'green')),
      	  'options'   => array(
              0 => Mage::helper('mturbo')->__('Not cached'),
              1 => Mage::helper('mturbo')->__('Cached')
          )
      ));
      
      $this->addColumn('last_refresh', array(
          'header'    => Mage::helper('mturbo')->__('Last refresh'),
          'align'     =>'left',
      	  'type' 	  => 'datetime',
          'index'     => 'last_refresh',
          'filter'	  => false,
          'sortable'  => false
      ));
      
      $this->addColumn('size', array(
          'header'    => Mage::helper('mturbo')->__('Size'),
          'align'     => 'left',
      	  'type' 	  => 'text',
          'index'     => 'page_size',
      	  'renderer'  => new Artio_MTurbo_Block_Data_Grid_Column_FileSize(),
          'filter'    => false,
          'sortable'  => false,
      ));

      $this->addColumn('blocked', array(
          'header'    => Mage::helper('mturbo')->__('Status'),
          'align'     => 'center',
          'index'     => 'blocked',
          'type'	  => 'options',
      	  'renderer'  => new Artio_MTurbo_Block_Data_Grid_Column_Blocked(),
          'options'   => array(
              0 => Mage::helper('mturbo')->__('Not blocked'),
              1 => Mage::helper('mturbo')->__('Blocked')
          ),
      ));
	   
      $this->addColumn('action2',
            array(
                'header'    => '',
                'width'     => '50px',
                'renderer'  => new Artio_MTurbo_Block_Data_Grid_Column_SwitchAction(),
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'captions' => array(Mage::helper('mturbo')->__('Purge')=>array('exist'=>true),
                        					Mage::helper('mturbo')->__('Cache')=>array('exist'=>false),
                        					'_'=>array('blocked'=>true)),
                        'url'     => array('base'    => '*/*/refresh',),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
       ));
	  

	   $this->addColumn('action3',
            array(
                'header'    => '',
                'width'     => '50px',
                'renderer'  => new Artio_MTurbo_Block_Data_Grid_Column_SwitchAction(),
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'captions' 	=> array(Mage::helper('mturbo')->__('Preview')=>array('exist'=>true),'_'=>array('exist'=>false)),
                        'url'      	=> array('base'=>'*/*/preview'),
                        'field'    	=> 'id',
                        'popup'		=> true
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
       ));

      return parent::_prepareColumns();
  }

    protected function _prepareMassaction() {

        $this->setMassactionIdField('mturbo_id');
        $this->getMassactionBlock()->setTemplate('mturbo/massaction.phtml');
        $this->getMassactionBlock()->setFormFieldName('mturbo');
        

        $this->getMassactionBlock()->addItem('block', array(
             'label'    => Mage::helper('mturbo')->__('Block'),
             'url'      => $this->getUrl('*/*/massBlock')
        ));
        
        $this->getMassactionBlock()->addItem('unblock', array(
             'label'    => Mage::helper('mturbo')->__('Unblock'),
             'url'      => $this->getUrl('*/*/massUnblock')
        ));
        
        /*$this->getMassactionBlock()->addItem('refresh', array(
             'label'    => Mage::helper('mturbo')->__('Cache'),
             'url'      => $this->getUrl('*//**//*massRefresh')
        ));*/
        
        $this->getMassactionBlock()->addItem('purge', array(
             'label'    => Mage::helper('mturbo')->__('Purge from disk'),
             'url'      => $this->getUrl('*/*/massPurge')
        ));
        
        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('mturbo')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('mturbo')->__('Are you sure?')
        ));

        return $this;
    }
    
	public function getMainButtonsHtml()
    {
        $html = '';

        $html.= Mage::getSingleton('core/layout')
                	->createBlock('adminhtml/widget_button', '', array(
                    	'label'   => Mage::helper('mturbo')->__('Cache selected pages'),
                    	'type'    => 'button',
                    	'onclick' => $this->_getOnClickCache()
                		))->toHtml();
        //$html.= '<input type="hidden" name="massrefresh" id="massrefresh" value="" />';
        $html.= Mage::getSingleton('core/layout')
                	->createBlock('adminhtml/widget_button', '', array(
                    	'label'   => Mage::helper('mturbo')->__('Synchronize'),
                    	'type'    => 'button',
                		'onclick' => $this->_getOnClickSynchronize()
                		))->toHtml();
        $html.= '<input type="hidden" name="massrefresh" id="massrefresh" value="" />';
        $html.= $this->getResetFilterButtonHtml();
        $html.= $this->getSearchButtonHtml();

        return $html;
    }
    
    private function _getOnClickSynchronize() {
    	return "$('massrefresh').value = url_section_massactionJsObject.checkedString;
    			this.form.action = '".Mage::helper('adminhtml')->getUrl('*/*/synchronize')."';
    		    this.form.submit();";
    }
    
    private function _getOnClickCache() {
    	return "if (url_section_massactionJsObject.checkedString.length > ".Mage::helper('mturbo')->getPostMaxValueSize().") 
    			{
    			  alert('". Mage::helper('mturbo')->__('You have checked too many pages. If you cache all pages use button `Cache all page` on `Action Tab`, please.') ."');
    			  return false;
    			}
    		    this.form.target='_blank';
    		    $('massrefresh').value = url_section_massactionJsObject.checkedString;
    		    this.form.action = '".Mage::helper('adminhtml')->getUrl('*/*/massRefresh')."';
    		    this.form.submit();
    		    this.form.target='_self';
    		    this.form.action = '".Mage::helper('adminhtml')->getUrl('*/*/index')."';
    		    this.form.submit();";
    }
  
	/**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('mturbo')->__('Url');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('mturbo')->__('Url');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

}
