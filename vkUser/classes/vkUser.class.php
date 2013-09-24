<?php

class vkUser extends activeRecord {

    private $_AppID;
    private $_AppSecret;
    private $_controllerUrl;
    private $_crypt;
    public $first_name;
    public $last_name;
    public $user_id;
    public $photo;

    public function __construct() {
        $this->_AppID = config::getValue('vkUser_AppID');
        $this->_AppSecret = config::getValue('vkUser_AppSecret');
        $this->_controllerUrl = config::getValue('vkUser_controllerUrl');
        if (!$this->_controllerUrl) $this->_controllerUrl = 'vklogin';
        $this->_crypt = new crypt();
        parent::__construct();
    }

    public function getOAuthUrl() {
        return "https://oauth.vk.com/authorize?client_id={$this->_AppID}&redirect_uri=http://{$_SERVER['HTTP_HOST']}/{$this->_controllerUrl}&response_type=code";
    }

    public function isAuthorized() {
        if ($this->id) return true;
        return $this->checkSign();
    }

    public function auth($code) {
        $token_url = "https://api.vkontakte.ru/oauth/access_token?client_id={$this->_AppID}&redirect_uri=http://{$_SERVER['HTTP_HOST']}/{$this->_controllerUrl}&client_secret={$this->_AppSecret}&code={$code}";
        $spam = json_decode(@file_get_contents($token_url));
        if ($spam->access_token) {
            $value = json_decode(@file_get_contents("https://api.vkontakte.ru/method/users.get?access_token=".$spam->access_token.'&uids='.$spam->user_id.'&fields=first_name,last_name,photo'));
            if ($value->response[0]->uid) {
                $this->load(array('user_id'=>$value->response[0]->uid));
                $this->user_id = $value->response[0]->uid;
                $this->first_name = $value->response[0]->first_name;
                $this->last_name = $value->response[0]->last_name;
                $this->photo = $value->response[0]->photo;
                $this->save();
                $this->_setSign();
                return true;
            }
        }
        return false;
    }


    public function checkSign() {
        $cookie = registry::getInstance()->getService('request')->cookie;
        if (!isset($cookie['vkUser_id'])||!isset($cookie['vkUser_expire'])||!isset($cookie['vkUser_sign'])) {
            return false;
        }
        $cookie_sign = $this->_crypt->hash($cookie['vkUser_id'], $cookie['vkUser_expire']);
        if ($cookie_sign != $cookie['vkUser_sign']){
            return false;
        }
        $this->id = $cookie['vkUser_id'];
        $this->load();
        if (!$this->user_id) return false;
        return true;
    }

    private function _setSign() {
        $id = $this->id;
        $expire = time() + (3600*24*30);
        $sign = $this->_crypt->hash($id, $expire);
        setcookie('vkUser_id', $id, $expire, '/');
        setcookie('vkUser_expire', $expire, $expire, '/');
        setcookie('vkUser_sign', $sign, $expire, '/');
    }

}

?>