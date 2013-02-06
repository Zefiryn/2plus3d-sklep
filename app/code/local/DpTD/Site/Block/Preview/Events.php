<?php

class DpTD_Site_Block_Preview_Events extends Mage_Core_Block_Template {

  protected $_events;

  protected function _construct() {
    parent::_construct();
    $this->setTemplate('page/preview/events.phtml');
  }

  public function getCollection() {
    if ($this->_events == null) {
      $this->setFullWidth(false);
      $nextDay = false;
      $previousDay = strftime('%Y-%m-%d', time() - 60 * 60 * 24);
      $comparison = '>';
      $order = 'ASC';
      $date = $previousDay;
      $dateEvents = Mage::getModel('site/event_date')->getCollection();
      $dateEvents ->getSelect()->columns(array('date' => new Zend_Db_Expr ('CAST(time AS DATE)')))->where("CAST(time AS DATE)$comparison?", $date)->group("date")->order('date ASC')->limit(2);
      $collectionCount = $dateEvents->load()->count();

      $where = "id IN (SELECT event_id FROM event_dates WHERE CAST(time AS DATE)=?) OR CAST(published_at AS DATE)=?";
      $index = 0;
      $days = array();
      foreach($dateEvents as $date){
        
        $events = Mage::getModel('site/event')->getCollection();
        $events->getSelect()->where($where, $date->getDate());
        if(!(($previousDay && $index == 0) || ($nextDay && $index == $collectionCount - 1))) {
          $events->getSelect()->limit(3);
        }
        
        $days[] = array('date' => $date->getDate(), 'events' => $events->load());
        
        if($events->count() > 3 && (($previousDay && $index == 0) || ($nextDay && $index == $collectionCount - 1))) {
          $this->setFullWidth(true);
          break;
        }
        $index++;
      }

      if($nextDay) {
        $days = array_reverse($days);
      }
      $this->_events = $days;
    }
    return $this->_events;
  }
}