<?php

declare(strict_types=1);

namespace Compalex;

use Compalex\Driver\Mysql;
use PDO;

/**
 * @property-read Config\Config $config
 */
class Compalex
{
    const WHICH_LEFT = 'left';
    const WHICH_RIGHT = 'right';

    /** @var Config\Config */
    protected $_config;

    /** @var DriverInterface */
    protected $_left;

    /** @var DriverInterface */
    protected $_right;

    protected function _getDriver(string $which)
    {
        switch($which) {
            default:
                throw new Exception('Invalid side ($which): '.$which);
            case self::WHICH_LEFT:
            case self::WHICH_RIGHT:
                switch($this->config->$which->driver) {
                    case 'mysql':
                        return new Mysql($this->config->$which);
                }
        }
    }

    public function __construct(Config\Config $config)
    {
        $this->_config = $config;
    }

    public function __get($name)
    {
        switch($name) {
            case 'config':
                return $this->_config;
        }
        return null;
    }

    /**
     * @return PDO
     * @throws Exception
     */
    public function getLeft()
    {
        if(!$this->_left) {
            $this->_left = $this->_getDriver(self::WHICH_LEFT);
        }
        return $this->_left;
    }

    /**
     * @return PDO
     * @throws Exception
     */
    public function getRight()
    {
        if(!$this->_right) {
            $this->_right = $this->_getDriver(self::WHICH_RIGHT);
        }
        return $this->_right;
    }
}