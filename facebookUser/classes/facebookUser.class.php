<?php

class facebookUser extends activeRecord {

    private $AppID;
    private $AppSecret;
    private $controllerUrl;
    private $_crypt;
    public $link;
    public $username;
    public $name;

    public function __construct() {
        $this->AppID = config::getValue('facebookUser_AppID');
        $this->AppSecret = config::getValue('facebookUser_AppSecret');
        $this->controllerUrl = config::getValue('facebookUser_controllerUrl');
        if (!$this->controllerUrl) $this->controllerUrl = 'fblogin';
        $this->_crypt = new crypt();
        parent::__construct();
    }

    public function setAppID($AppID) {
        $this->AppID = $AppID;
    }

    public function setAppSecret($AppSecret) {
        $this->AppSecret = $AppSecret;
    }

    public function getOAuthUrl() {
        return "https://www.facebook.com/dialog/oauth?client_id={$this->AppID}&redirect_uri=http://{$_SERVER['HTTP_HOST']}/{$this->controllerUrl}&response_type=code";
    }

    public function isAuthorized() {
        if ($this->id) return true;
        return $this->checkSign();
    }

    public function auth($code) {
        $token_url = "https://graph.facebook.com/oauth/access_token?client_id={$this->AppID}&redirect_uri=http://{$_SERVER['HTTP_HOST']}/{$this->controllerUrl}&client_secret={$this->AppSecret}&code={$code}";
        $spam = explode('&', @file_get_contents($token_url));
        for ($i=0, $j=count($spam); $i<$j; $i++) {
            $params = explode('=', $spam[$i]);
            if ($params[0]=='access_token') {
                $value = json_decode(@file_get_contents("https://graph.facebook.com/me?access_token=".$params[1]));
                if ($value->username) {
                    $this->load(array('username'=>$value->username));
                    $this->init();
                    $this->save();
                    $this->_setSign();
                    return true;
                }
            }
        }
        return false;
    }

    public function checkSign() {
        $cookie = registry::getInstance()->getService('request')->cookie;
        if (!isset($cookie['facebookUser_id'])||!isset($cookie['facebookUser_expire'])||!isset($cookie['facebookUser_sign'])) {
            return false;
        }
        $cookie_sign = $this->_crypt->hash($cookie['facebookUser_id'], $cookie['facebookUser_expire']);
        if ($cookie_sign != $cookie['facebookUser_sign']){
            return false;
        }
        $this->id = $cookie['facebookUser_id'];
        $this->load();
        return true;
    }

    private function _setSign() {
        $id = $this->id;
        $expire = time() + (3600*24*30);
        $sign = $this->_crypt->hash($id, $expire);
        setcookie('facebookUser_id', $id, $expire, '/');
        setcookie('facebookUser_expire', $expire, $expire, '/');
        setcookie('facebookUser_sign', $sign, $expire, '/');
    }

}

?>