<?php

class activeRecord extends component {

    private $id;
    private $_connector;
    private $_host;
    private $_login;
    private $_password;
    private $_database;
    private $_table;

    public function __construct() {
        $this->_host = !$this->_host?config::getValue('mysql_host'):$this->_host;
        $this->_login = !$this->_login?config::getValue('mysql_login'):$this->_login;
        $this->_password = !$this->_password?config::getValue('mysql_password'):$this->_password;
        $this->_database = !$this->_database?config::getValue('mysql_database'):$this->_database;
        $this->_connector = new mysqli($this->_host, $this->_login, $this->_password, $this->_database);
        $this->_connector->query('set names utf8');
        $this->_table = get_class($this);
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
        $sql = 'select * from '.$this->_table.' where 1=1';
        foreach ($criteria as $k=>$v) {
            $sql .= " and $k = '$v'";
        }
        $result = $this->_connector->query($sql);
        if ($result->num_rows>0) {
            $spam = $result->fetch_assoc();
            $this->init($spam);
        }
    }

    public function delete() {
        if (!$this->id) {
            throw new RuntimeException("There is no criteria for selecting an object");
        }
        $sql = 'delete from '.$this->_table.' where id = '.intval($this->id);
        $result = $this->_connector->query($sql);
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

    private function _updateSQL() {
        $spam = $this->asArray();
        unset($spam['id']);
        $sql = 'update '.$this->_table.' set';
        $i=0;
        foreach($spam as $k=>$v) {
            $sql .= ($i>0?', ':' ')."`$k`='".str_replace(array("\'", "'"),"\'",(is_array($v)?serialize($v):$v))."'";
            $i = $i+1;
        }
        $sql .= ' where id = '.$this->id;
        return $sql;
    }

    private function _insertSQL() {
        $spam = $this->asArray();
        unset($spam['id']);
        if (!$this->id>0) {
            $result = $this->_connector->query('select max(id) as maxid from '.$this->_table);
            $tmp = $result->fetch_assoc();
            $this->id = $tmp['maxid']>0?$tmp['maxid']+1:1;
        }
        $sql = 'insert into '.$this->_table.' (id';
        $sql_values = 'values ('.$this->id;
        foreach ($spam as $k=>$v) {
            $sql .= ', `'.$k.'`';
            $sql_values .= ", '".str_replace(array("'", "\'"), "\'", (is_array($v)?serialize($v):$v))."'";
        }
        $sql = $sql.') '.$sql_values.')';
        return $sql;
    }

    public function save() {
        if ($this->id>0) {
            $sql = $this->_updateSQL();
            $this->_connector->query($sql);
            if ($this->_connector->affected_rows==0) {
                $list = new activeList($this->_table);
                $count = $list->setQuery(array('id'=>$this->id))->getCount();
                if ($count==0) {
                    $sql = $this->_insertSQL();
                    $this->_connector->query($sql);
                }
            }
        } else {
            $sql = $this->_insertSQL();
            $this->_connector->query($sql);
        }
    }

}

?>
