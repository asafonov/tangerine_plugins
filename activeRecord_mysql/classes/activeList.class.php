<?php
class activeList extends component {
    private $fields=array();
    private $query=array();
    private $limit = 0;
    private $skip = 0;
    private $order = array();
    private $distinct = false;
    private $_connector;
    private $_host;
    private $_login;
    private $_password;
    private $_database;
    private $_table;
    private $_operands = array('>'=>true, '<'=>true, '<>'=>true, '>='=>true, '<='=>true);

    public function __construct($table = false) {
        $this->_host = !$this->_host?config::getValue('mysql_host'):$this->_host;
        $this->_login = !$this->_login?config::getValue('mysql_login'):$this->_login;
        $this->_password = !$this->_password?config::getValue('mysql_password'):$this->_password;
        $this->_database = !$this->_database?config::getValue('mysql_database'):$this->_database;
        $this->_connector = new mysqli($this->_host, $this->_login, $this->_password, $this->_database);
        $this->_connector->query('set names utf8');
        $this->_table = $table?$table:get_class($this);
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

    private function _createSQL($count=false) {
        if ($count) {
            $sql = 'select count(1) ';
        } else {
            $sql = 'select '.($this->distinct?' distinct ':'').
            (count($this->fields)>1?implode('`, `', $this->fields):count($this->fields)==1?'`'.$this->fields[0].'`':'*').' ';
        }
        $sql .= 'from '.$this->_table;
        if (count($this->query)>0) {
            $sql .= ' where 1=1';
            foreach ($this->query as $k=>$v) {
                if (is_array($v)) {
                    if ($k=='or') {
                        $sql .= ' and (1=0';
                        foreach ($v as $field=>$value) {
                            $sql .= ' or `'.$field.'` = \''.str_replace(array("'", "\'"), "\'", $value).'\'';
                        }
                        $sql .= ')';
                    } elseif (isset($this->_operands[$k])) {
                        $field = array_keys($v);
                        $value = array_values($v);
                        $sql .= " and `{$field[0]}` $k '".str_replace(array("'", "\'"), "\'", $value[0])."'";
                    } else {
                        $sql .= " and `$k` in ('".implode("', '", $v)."')";
                    }
                } else {
                    $sql .= " and `$k` = '".str_replace(array("'", "\'"), "\'", $v)."'";
                }
            }
        }
        if (count($this->order)>0) {
            $i=0;
            $sql .= ' order by ';
            foreach($this->order as $k=>$v) {
                $sql .= ($i>0?', ':'').$k.(!$v||$v<0?' desc':'');
                $i=$i+1;
            }
        }
        $sql .= $this->limit>0?' limit '.intval($this->skip).', '.$this->limit:'';
        return $sql;
    }

    public function asArray() {
        $sql = $this->_createSQL();
        $result = $this->_connector->query($sql);
        $ret = array();
        while ($spam = $result->fetch_assoc()) {
            $ret[] = $spam;
        }
        return $ret;
    }

    public function  getCount() {
        $sql = $this->_createSQL(true);
        $result = $this->_connector->query($sql);
        $spam = $result->fetch_array();
        return $spam[0];
    }

    public function getRand() {
        $this->order = array();
        $limit = max(1, intval($this->limit));
        $this->limit = 0;
        $sql = $this->_createSQL();
        $sql .= ' order by rand() limit '.$limit;
        $result = $this->_connector->query($sql);
        $ret = array();
        while ($spam = $result->fetch_assoc()) {
            $ret[] = $spam;
        }
        return $ret;
    }
}
?>
