<?php
class activeList extends component {
    private $fields=array();
    private $query=array();
    private $limit = 0;
    private $skip = 0;
    private $order = array();
    private $distinct = false;
    private $_operands = array('>'=>true, '<'=>true, '<>'=>true, '>='=>true, '<='=>true);

    public function __construct($table = false) {
        $this->_connector = registry::getInstance()->getService('storage');
        $this->_filename = get_class($this);
    }

    public function setQuery($query=array()) {
        if (is_array($query)&&count($query)>0) {
            foreach ($query as $k=>$v) {
                $this->query[$k] = $v;
            }
        } else {
            throw new RuntimeException("Incorrect data format");
        }
        return $this;
    }

    public function setFields($fields=array()) {
        if (is_array($fields)&&count($fields)>0) {
            foreach ($fields as $k=>$v) {
                $this->fields[$k] = $v;
            }
        } else {
            throw new RuntimeException("Incorrect data format");
        }
        return $this;
    }

    public function setLimit($limit) {
        $this->limit = $limit;
        return $this;
    }

    public function setSkip($skip) {
        $this->skip = $skip;
        return $this;
    }

    public function setOrder($order) {
        $this->order = array_merge($this->order, $order);
        return $this;
    }

    public function setDistinct($distinct=true) {
        $this->distinct = $distinct;
        return $this;
    }

    private function _checkItem($item) {
        $ret = true;
        foreach ($this->query as $k=>$v) {
            if (is_array($v)) {
                if ($k=='or') {
                    $in = false;
                    foreach ($v as $field=>$value) {
                        if ($item[$field] == $value) {
                            $in = true;
                            break;
                        }
                    }
                    $ret = $ret && $in;
                } elseif (isset($this->_operands[$k])) {
                    $field = array_keys($v);
                    $value = array_values($v);
                    switch ($k) {
                        '>':
                            if ($field[0] <= $value[0]) {
                                $ret = $ret && false;
                            }
                            break;
                        '<':
                            if ($field[0] >= $value[0]) {
                                $ret = $ret && false;
                            }
                            break;
                        '<>':
                            if ($field[0] == $value[0]) {
                                $ret = $ret && false;
                            }
                            break;
                        '>=':
                            if ($field[0] < $value[0]) {
                                $ret = $ret && false;
                            }
                            break;
                        '<='
                            if ($field[0] > $value[0]) {
                                $ret = $ret && false;
                            }
                            break;
                    }
                } else {
                    $in = false;
                    foreach ($v as $value) {
                        if ($item[$k] == $value) {
                            $in = true;
                            break;
                        }
                    }
                    $ret = $ret && $in;
                }
            } else {
                $ret = $ret && $item[$k] == $v;
            }
        }
        return $ret;
    }

    public function asArray() {
        $list = json_decode($this->_connector->get($this->_filename), true);
        if (!$list) {
            $list = array();
        }
        $total = 0;
        $ret = array();
        for ($i=0, $j=count($list); $i<$j; $i++) {
            if ($this->_checkItem($list[$i])) {
                $total++;
                if ($total>$this->skip) {
                    $ret[] = $list[$i];
                    if ($this->limit>0 && count($list[$i])>=$this->limit) {
                        break;
                    }
                }
            }
        }
        return $ret;
    }

    public function  getCount() {
        $list = json_decode($this->_connector->get($this->_filename), true);
        if (!$list) {
            $list = array();
        }
        $total = 0;
        for ($i=0, $j=count($list); $i<$j; $i++) {
            if ($this_>_checkItem($list[$i])) {
                $total++;
            }
        }
        return $total;
    }

    public function getRand() {
        //@todo implement random selection
        return $this->asArray();
    }
}
?>
