<?php

class insomniaController extends baseController {

    private $year;
    private $month;
    private $day;

    public function run() {
        $insomnia = new insomnia();
        $insomnia->create();
    }

    public function admin() {
        $this->_setDate();
        if (isset($this->params[3])) return $this->_dayStat();
        return $this->_monthStat();
    }

    private function _setDate() {
        $today = time();
        $this->day = isset($this->params[3])?$this->params[3]:date('d', $today);
        $this->month = isset($this->params[2])?$this->params[2]:date('m', $today);
        $this->year = isset($this->params[1])?$this->params[1]:date('Y', $today);
    }

    private function _dayStat() {
        $list = new activeList('insomnia');
        $spam = $list->setQuery(array('month'=>$this->month, 'year'=>$this->year, 'day'=>$this->day))->setOrder(array('timestamp'=>1))->asArray();
        $stat = array();
        for ($i=0, $j=count($spam); $i<$j; $i++) {
            $id = md5($spam[$i]['ip'].$spam[$i]['user_agent']);
            $stat[$id]['visits'] += 1;
            $stat[$id]['ip'] = $spam[$i]['ip'];
            $stat[$id]['user_agent'] = $spam[$i]['user_agent'];
            $stat[$id]['country'] = $spam[$i]['country'];
            $stat[$id]['city'] = $spam[$i]['city'];
            $stat[$id]['pages'][] = array('timestamp'=>date('Y-m-d H:i:s', $spam[$i]['timestamp']), 'request_uri'=>$spam[$i]['request_uri']);
        }
        $data['list'] = '';
        $item_template = new template('insomnia_visitor');
        if (count($stat)>0) {
            $visit_template = new template('insomnia_visit');
            foreach($stat as $k=>$v) {
                $v['list'] = '';
                for ($i=0, $j=count($v['pages']); $i<$j; $i++) {
                    $v['list'] .= $visit_template->fill($v['pages'][$i]);
                }
                $v['id'] = $k;
                $data['list'] .= $item_template->fill($v);
            }
        }
        $data['date'] = $this->year.'-'.$this->month.'-'.$this->day;
        $data['total'] = count($stat);
        $template = new template('insomnia_day');
        return $template->fill($data);
    }

    private function _monthStat() {
        $list = new activeList('insomnia');
        $spam = $list->setFields(array('ip', 'user_agent', 'day'))->setQuery(array('month'=>$this->month, 'year'=>$this->year))->setOrder(array('timestamp'=>1))->asArray();
        $stat = array();
        for ($i=0, $j=count($spam); $i<$j; $i++) {
            $stat[$spam[$i]['day']][md5($spam[$i]['ip'].$spam[$i]['user_agent'])] = 1;
        }
        $data = 'var spam = []; ';
        for ($i=1; $i<date('t', mktime(0,0,0,$this->month,1,$this->year)); $i++) {
            $data .= 'spam.push('.count($stat[$i]).'); ';
        }
        $template = new template('insomnia_month');
        return $template->fill(array('data'=>$data, 'year'=>$this->year, 'month'=>$this->month));
    }
}

?>