<?php

class insomnia extends activeRecord {
    public $timestamp;
    public $year;
    public $month;
    public $day;
    public $ip;
    public $user_agent;
    public $request_uri;
    public $cookies = array();
    public $country;
    public $city;
    private $_geoipProvider;

    public function __construct() {
        parent::__construct();
        $this->_geoipProvider = registry::getInstance()->getService('geoipProvider');
    }

    public function create() {
        $this->timestamp = time();
        $this->year = date('Y', $this->timestamp);
        $this->month = date('m', $this->timestamp);
        $this->day = date('d', $this->timestamp);
        $this->ip = $_SERVER['REMOTE_ADDR'];
        $this->user_agent = $_SERVER['HTTP_USER_AGENT'];
        $this->request_uri = $_SERVER['REQUEST_URI'];
        $this->cookies = registry::getInstance()->getService('request')->cookie;
        $this->country = $this->_geoipProvider->getCountry($this->ip);
        $this->city = $this->_geoipProvider->getCity($this->ip);
        $this->save();
    }
}

?>