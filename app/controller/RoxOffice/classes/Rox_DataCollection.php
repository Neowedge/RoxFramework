<?php
namespace RoxOffice\Controllers;

use Iterator;

class Rox_DataCollection implements Iterator
{
    private $_data = array();

    public function add($value, $display)
    {
        $this->_data[$value] = new Rox_Data($value, $display);
    }

    public function remove($value)
    {
        unset($this->_data[$index]);
    }

    public function get($value)
    {
        return $this->_data[$value];
    }

    public function current()
    {
        return current($this->_data);
    }

    public function next()
    {
        next($this->_data);
    }

    public function all()
    {
        return $this->_data;
    }

    public function key()
    {
        return key($this->_data);
    }

    public function valid()
    {
        if ($this->_data) {
            return true;
        }
        return false;
    }

    public function rewind()
    {
        reset($this->_data);
    }
}