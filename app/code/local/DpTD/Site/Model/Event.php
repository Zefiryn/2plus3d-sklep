<?php
/**
 * Events Model
 *
 * @category   Site
 * @package    DpTD_Site
 * @author     Zefiryn <zefiryn@jewula.net>
 *
 */


class DpTD_Site_Model_Event extends Mage_Core_Model_Abstract {

  protected $_dates;

  public function _construct()
  {
    parent::_construct();
    $this->_init('site/event');
  }

  public function getDates($type = null)
  {
    if($this->_dates == null  && $this->getId()) {
      $eventDateCollection = Mage::getModel('site/event_date')->getCollection();
      $eventDateCollection->getSelect()->order('time ASC')->where('event_id=?', $this->getId());
      
      if($eventDateCollection->count() > 0) {
        $this->_dates = $eventDateCollection;
      } else {
        $this->_dates = false;
      }
    }
    
    if(!empty($type)) {
      $dates = array();
      
      if($this->_dates) {
        foreach($this->_dates as $date) {
          if($date->getType() == $type) {
            $dates[] = $date;
          }
        }
      }
      
      if(count($dates) == 1) {
        $dates = $dates[0];
      } elseif(empty($dates)) {
        $dates = false;
      }
      
      return $dates;
    } 
    else {
      return $this->_dates;
    }
  }

  public function getDateByTime($time, $dateComparisonOnly = true)
  {
    foreach($this->getDates() as $date) {
      $datesTime = $dateComparisonOnly ? substr($date->getTime(), 0, 10) : $date->getTime();

      if($datesTime == $time) {
        return $date;
      }
    }

    return false;
  }

  public function getLocation()
  {
    $city = Mage::getModel('site/city')->load($this->getCityId());

    $result = '';
    
    if($city->getId()) {
      $result .= $city->getName();
      
      if($city->getParentId() && $city->getParent()->getName() != 'Polska') {
        $result .= ', ' . $city->getParent()->getName();
      }
    }

    return $result;
  }
}