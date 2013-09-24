<?php

class vkUserController extends baseController {

    public function run() {
        if (isset($this->query['code'])) {
            $vkUser = registry::getInstance()->getService('vkUser');
            $result = $vkUser->auth($this->query['code']);
            if ($result) {
                $success_url=config::getValue('vkUser_success_url');
                if (!$success_url) $success_url = '/';
                $this->Location($success_url);
            } else {
                return 'OAuth Error';
            } 
        }
    }

}

?>