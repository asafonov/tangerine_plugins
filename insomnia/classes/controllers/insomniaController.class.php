<?php

class insomniaController extends baseController {
    public function run() {
        $insomnia = new insomnia();
        $insomnia->create();
    }

    public function admin() {
        return $this->_monthStat();
    }

    private function _monthStat() {
        $today = time();
        $month = date('m', $today);
        $year = date('Y', $today);
        $list = new activeList('insomnia');
        $spam = $list->setFields(array('ip', 'user_agent'))->setQuery(array('month'=>$month, 'year'=>$year))->setOrder(array('timestamp'=>1))->asArray();
        print_r($spam);

    }
}

?>