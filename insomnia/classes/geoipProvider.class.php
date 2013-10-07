<?php

class geoipProvider extends activeRecord {
    
    public $ip;
    public $country;
    public $city;

    public function getCountry($ip) {
        if ($this->ip != $ip) {
            $this->load(array('ip'=>$ip));
        }
        if (!$this->id) {
            $this->ip = $ip;
            $this->_loadFromSource();
        }
        return $this->country;
    }

    private function _loadFromSource() {
        $spam = @json_decode(@file_get_contents("http://smart-ip.net/geoip-json?host=".$this->ip));
        if ($spam->countryCode) {
            $this->country = $spam->countryCode;
            $this->city = $spam->city;
            $this->save();
        }
    }

    public function getCity($ip) {
        if ($this->ip != $ip) {
            $this->load(array('ip'=>$ip));
        }
        if (!$this->id) {
            $this->ip = $ip;
            $this->_loadFromSource();
        }
        return $this->city;
    }


}

?>