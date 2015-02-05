<?php

class DpTD_Site_Helper_Data extends Mage_Core_Helper_Abstract {

    const SITE_URL_XML_PATH = 'dptd_site/general/site_address';
    const PROTOCOL_MATCH_XML_PATH = 'dptd_site/general/protocol_match';

    protected $_siteUrl = null;

    protected $_menuUrl = array(
        'types' => array('articles' => 'artykuly', 'events' => 'kalendarium', 'issues' => 'kwartalnik', 'sitemap' => 'mapa'),
        'models' => array('issues' => 'number', 'events' => array())
    );

    protected $_typeModels = array(
        'pages' => 'site/page',
        'issues' => 'site/issue',
        'events' => 'site/event',
    );

    public function getSiteBaseUrl() {
        if($this->_siteUrl == null) {
            $this->_siteUrl = Mage::getStoreConfig(self::SITE_URL_XML_PATH);
            if(Mage::getStoreConfig(self::PROTOCOL_MATCH_XML_PATH)) {
                $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                $this->_siteUrl = $protocol . substr($this->_siteUrl, strpos($this->_siteUrl, '://') + 3);
            }
        }

        return $this->_siteUrl;
    }

    public function ago($date, $limit = 1, $startPeriod = 0) {
        $locale = Mage::app()->getLocale()->getLocaleCode();
        setlocale(LC_ALL, "{$locale}.UTF8");
        if(empty($date)) {
            return "No date provided";
        }
        //
        //     $periods         = array(
        //  array("format" => "%S", "names" => array("sekundę", "sekundy", "sekund")),
        //  array("format" => "%M", "names" => array("minutę", "minuty", "minut")),
        //  array("format" => "%H", "names" => array("godzinę", "godziny", "godzin")),
        //  array("format" => "%j", "names" => array("dzień", "dni", "dni")),
        //  array("format" => "%W", "names" => array("tydzień", "tygodnie", "tygodni", "tygodniu")),
        //  array("format" => "%m", "names" => array("miesiąc", "miesiące", "miesięcy", "miesiącu")),
        //  array("format" => "%Y", "names" => array("rok", "lata", "lat", "roku"))
        // );
        // $lengths         = array(0, 60, 60, 24, 4.35, 12);

        $now = time();
        $unixDate = strtotime($date . ' UTC');

        // check validity of date
        if(empty($unixDate)) {
            return "Bad date";
        }

        $difference = floor($now / 86400) - floor($unixDate / 86400);
        if($difference == 0) {
            return "dzisiaj";
        }
        elseif(abs($difference) == 1) {
            return ($difference >= 0) ? "wczoraj" : "jutro";
        }

        $from = array(
            'styczeń', 'luty', 'marzec', 'kwiecień', 'maj', 'czerwiec', 'lipiec',
            'sierpień', 'wrzesień', 'październik', 'listopad', 'grudzień'
        );

        $to = array(
            'stycznia', 'lutego', 'marca', 'kwietnia', 'maja', 'czerwca', 'lipca',
            'sierpnia', 'września', 'października', 'listopada', 'grudnia'
        );

        return str_replace($from, $to, strftime('%e&nbsp;%B&nbsp;%Y', $unixDate));
    }

    function truncate($string, $length = 80, $etc = '...', $break_words = false, $middle = false) {
        if($length == 0) {
            return '';
        }

        if(is_callable('mb_strlen')) {
            if(mb_detect_encoding($string, 'UTF-8, ISO-8859-1') === 'UTF-8') {
                // $string has utf-8 encoding
                if(mb_strlen($string) > $length) {
                    $length -= min($length, mb_strlen($etc));
                    if(!$break_words && !$middle) {
                        $string = preg_replace('/\s+?(\S+)?$/u', '', mb_substr($string, 0, $length + 1));
                    }
                    if(!$middle) {
                        return mb_substr($string, 0, $length) . $etc;
                    }
                    else {
                        return mb_substr($string, 0, $length / 2) . $etc . mb_substr($string, -$length / 2);
                    }
                }
                else {
                    return $string;
                }
            }
        }
        // $string has no utf-8 encoding
        if(strlen($string) > $length) {
            $length -= min($length, strlen($etc));
            if(!$break_words && !$middle) {
                $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length + 1));
            }
            if(!$middle) {
                return substr($string, 0, $length) . $etc;
            }
            else {
                return substr($string, 0, $length / 2) . $etc . substr($string, -$length / 2);
            }
        }
        else {
            return $string;
        }
    }

    public function menuItemUrl($params) {
        $item = isset($params['item']) ? $params['item'] : null;

        if($item == null) {
            if(isset($params['type'])) {
                $url = $this->_menuUrl['types'][$params['type']];
                if(isset($params['arg'])) {
                    $urlParams = $this->_menuUrl['models'][$params['type']];
                    $urlParams = is_array($urlParams) ? $urlParams : array($urlParams);
                    foreach($urlParams as $attribute) {
                        $url .= "/" . $params['arg']->getData($attribute);
                    }
                }
            }
            else {
                return null;
            }
        }
        else {
            $parameters = $item->getParametersArray();
            if(count($parameters) == 0) {
                $url = isset($this->_menuUrl['types'][$item->getType()]) ? $this->_menuUrl['types'][$item->getType()] : null;
            }
            else {
                $model = Mage::getModel($this->_typeModels[$item->getType()]);
                if(isset($parameters['action'])) {
                    switch($parameters['action']) {
                        case 'show' :
                            $url = $model->load($parameters['id'])->getPermalink();
                            break;
                    }
                }
            }
        }
        if($url) {
            return $this->getSiteBaseUrl() . $url;
        }
        else {
            return null;
        }
    }
}