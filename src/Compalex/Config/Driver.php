<?php

declare(strict_types=1);

namespace Compalex\Config;

/**
 * @property string $host
 * @property int $port
 * @property string $database
 * @property string $username
 * @property string $password
 * @property string $charset
 */
class Driver extends Config
{
    protected function _getRequiredKeys(): array
    {
        return ['host', 'database'];
    }

    protected function _getDefaultValues(): array
    {
        return [
            'port' => 3306,
            'charset' => 'utf8',
        ];
    }

    /**
     * @return string
     */
    public function buildDsn(): string
    {
        $dsn = $this->driver . ':host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->database;
        if($this->driver !== 'pgsql') {
            $dsn.= ';charset=' . $this->charset;
        }
        return $dsn;
    }
}