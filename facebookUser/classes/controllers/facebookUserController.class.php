<?php

class facebookUserController extends baseController {

    public function login() {
        $facebookUser = new facebookUser();
        $template = new template('facebookUser_login');
        return $template->fill(array('url'=>$facebookUser->getOAuthUrl()));
    }

    public function run() {
        if (isset($this->query['code'])) {
            $facebookUser = registry::getInstance()->getService('facebookUser');
            $result = $facebookUser->auth($this->query['code']);
            if ($result) {
                $success_url=config::getValue('facebookUser_success_url');
                if (!$success_url) $success_url = '/';
                $this->Location($success_url);
            } else {
                return 'OAuth Error';
            } 
        }
    }

}

?>