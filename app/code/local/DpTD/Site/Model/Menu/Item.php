<?php
/**
 * Menu Item Model
 *
 * @category   Menu
 * @package    DpTD_Menu
 * @author     Zefiryn <zefiryn@jewula.net>
 *
 * Fields:
 * id
 * parent_id - id of the parent menu item
 * type - type of the menu item
 * title - title shown of the frontend
 * position - position in the menu (according to its own level)
 * preview 
 * published - enable/disable status
 * container - part of the page where menu item should be displayed
 * restrict_to_role - should be visible on frontend *
 */


class DpTD_Site_Model_Menu_Item extends Mage_Core_Model_Abstract {

  protected $_types = array(
    'articles'  => array('title' => 'Archiwum artykułów'),
    'authors'   => array('title' => 'Lista autorów'),
    'modules'   => array('title' => 'Panel administracyjny'),
    'pages'     => array('title' => 'Lista stron'),
    'issues'    => array('title' => 'Lista numerów kwartalnika'),
    'events'    => array('title' => 'Kalendarium'),
    'sitemap'   => array('title' => 'Mapa serwisu')
  );

  public function _construct()
  {
    parent::_construct();
    $this->_init('site/menu_item');
  }

  public function getMainMenuTree() {
    $collection = $this->getCollection()
                        ->addFieldToFilter('container', array('eq' => 'main'))
                        ->addFieldToFilter('published', array('eq' => 1))
                        ->setOrder('parent_id, position', 'ASC');
    $menu = array();
    $level_position = array();
    foreach($collection as $item) {
      if ($item->getParentId() == null && $item->getRestrictToRole() == null) {
        $level_position[$item->getId()] = $item->getPosition();
        $menu[$item->getPosition()]['element'] = $item;
      }
      else {
        if (array_key_exists($item->getParentId(), $level_position)) {
          $idx = $level_position[$item->getParentId()];          
          $menu[$idx]['sublevel'][] = $item;
        }
      }
    }
    return $menu;
  }

  public function getFooterMenuTree() {
    $collection = $this->getCollection()
                        ->addFieldToFilter('container', array('eq' => 'footer'))
                        ->addFieldToFilter('published', array('eq' => 1))
                        ->setOrder('parent_id, posiion', 'ASC');
    $menu = array();
    $level_position = array();
    foreach($collection as $item) {
      if ($item->getParentId() == null && $item->getRestrictToRole() == null) {
        $level_position[$item->getId()] = $item->getPosition();
        $menu[$item->getPosition()]['element'] = $item;
      }
      else {
        $idx = $level_position[$item->getParentId()];
        if (array_key_exists($idx, $menu)) {
          $menu[$idx]['sublevel'][] = $item;
        }
      }
    }
    return $menu;
  }

}