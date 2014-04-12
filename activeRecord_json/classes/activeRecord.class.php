<?php

class activeRecord extends component {

    private $id;
    private $_connector;
    private $_filename;

    public function __construct() {
        $this->_connector = registry::getInstance()->getService('storage');
        $this->_filename = get_class($this);
    }

    public function create($data = array()) {
        if (count($data)==0) {
            $data = registry::getInstance()->getService('request')->query;
        }
        $this->init($data);
        $this->save();
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function init($data=array()) {
        if (count($data)>0) {
            foreach ($data as $k=>$v) {
                $method_name = 'set'.$k;
                if (method_exists($this, $method_name)) {
                    if (is_array($this->$k)) {
                        $this->$method_name(unserialize($v));
                    } else {
                        $this->$method_name($v);
                    }
                } elseif (property_exists($this, $k)) {
                    if (is_array($this->$k)) {
                        $this->$k = is_array($v)?$v:unserialize($v);
                    } else {
                        $this->$k = $v;
                    }
                }
            }
        }
    }

    public function load($criteria = null) {
        if ($criteria == null) {
            if ($this->id) {
                $criteria = array('id'=>$this->id);
            } else {
                throw new RuntimeException("There is no criteria for selecting an object");
            }
        }
        $list = json_decode($this->_connector->get($this->_filename), true);
        $result = null;
        for ($i=0, $j=count($list); $i<$j; $i++) {
            $found = true;
            foreach ($criteria as $k=>$v) {
                if ($list[$i][$k] != $v) {
                    $found = false;
                    break;
                }
            }
            if ($found) {
                $result = $list[$i];
                break;
            }
        }
        return $result;
    }

    public function delete() {
        if (!$this->id) {
            throw new RuntimeException("There is no criteria for selecting an object");
        }
        $list = json_decode($this->_connector->get($this->_filename), true);
        $result = null;
        for ($i=0, $j=count($list); $i<$j; $i++) {
            $found = true;
            foreach ($criteria as $k=>$v) {
                if ($list[$i][$k] != $v) {
                    $found = false;
                    break;
                }
            }
            if ($found) {
                $list = array_slice($list, $i, 1);
                break;
            }
        }
        $this->_connector->set($this->_filename, json_encode($result));
        return $true;
    }

    public function asArray() {
        $string=  var_export($this, true);
        $string=  str_replace('))', ')', $string);
        $string=  preg_replace('/[a-z0-9_]+::__set_state\(/si', '', $string);
        eval('$spam='.$string.';');
        foreach ($spam as $k=>$v) {
            if (strpos($k, '_')===0) {
                unset($spam[$k]);
            }
        }
        return $spam;
    }

    public function save() {
        $list = json_decode($this->_connector->get($this->_filename), true);
        if (!$list) {
            $list = array();
        }
        if ($this->id) {
            for ($i=0, $j=count($list); $i<$j; $i++) {
                if ($list[$i]['id'] == $this->id) {
                    $list[$i] = $this->asArray();
                }
            }
        } else {
            $this->id = uniqid();
            $list[] = $this->asArray();
        }
        $this->_connector->set($this->_filename, json_encode($list));
        return $true;
    }

}

?>
