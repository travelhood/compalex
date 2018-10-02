<?php

namespace Compalex;

use ArrayAccess;

abstract class Config implements ArrayAccess
{
    /** @var array */
    private $_data = [];

    /**
     * @return array
     */
    protected function _getDefaultValues() : array
    {
        return [];
    }

    /**
     * @return array
     */
    protected function _getRequiredKeys() : array
    {
        return [];
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function _processKey(string $key, $value)
    {
        return $value;
    }

    /**
     * @param array $config
     * @throws Config\Exception
     */
    public function __construct(array $config)
    {
        $this->_data = $this->_getDefaultValues();
        foreach($config as $k=>$v) {
            $this->_data[$k] = $this->_processKey($k, $v);
        }
        $this->validate();
    }

    /**
     * @param array $config
     * @return $this
     */
    public function merge(array $config) : self
    {
        foreach($config as $k=>$v) {
            $config[$k] = $this->_processKey($k, $v);
        }
        return $this;
    }

    /** @inheritdoc */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_data);
    }

    /** @inheritdoc */
    public function offsetGet($offset)
    {
        if($this->offsetExists($offset)) {
            return $this->_data[$offset];
        }
        return null;
    }

    /** @inheritdoc */
    public function offsetSet($offset, $value)
    {
        $this->_data[$offset] = $value;
    }

    /** @inheritdoc */
    public function offsetUnset($offset)
    {
        if($this->offsetExists($offset)) {
            unset($this->_data[$offset]);
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    /**
     * @param string $name
     */
    public function __unset($name)
    {
        $this->offsetUnset($name);
    }

    /**
     * @return $this
     * @throws Config\Exception
     */
    public function validate() : self
    {
        foreach($this->_getRequiredKeys() as $key) {
            if(!$this->offsetExists($key)) {
                throw new Config\Exception('Missing required key: '.$key);
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function asArray() : array
    {
        $r = $this->_data;
        foreach($r as $k => $v) {
            if(is_object($v)) {
                $r[$k] = (array)$v;
            }
            elseif($v instanceof self) {
                $r[$k] = $v->asArray();
            }
        }
        return $r;
    }

    /**
     * @return object
     */
    public function asObject()
    {
        $r = (object)$this->_data;
        foreach($r as $k => $v) {
            if(is_array($v)) {
                $r[$k] = (object)$v;
            }
            elseif($v instanceof self) {
                $r[$k] = $v->asObject();
            }
        }
        return $r;
    }
}