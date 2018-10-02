<?php

declare(strict_types=1);

namespace Compalex\Config;

use Compalex\Config as BaseConfig;

/**
 * @property bool $debug
 * @property int $sample_row_count
 * @property string $adapter
 * @property Driver $left
 * @property Driver $right
 */
class Config extends BaseConfig
{
    const LEFT_NAME = 'left';
    const RIGHT_NAME = 'right';

    protected function _getRequiredKeys(): array
    {
        return [self::LEFT_NAME, self::RIGHT_NAME];
    }

    protected function _getDefaultValues(): array
    {
        return [
            'debug' => false,
            'sample_row_count' => 100,
            'adapter' => 'mysql',
        ];
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return Driver|mixed
     * @throws Exception
     */
    protected function _processKey(string $key, $value)
    {
        if($key == self::LEFT_NAME || $key == self::RIGHT_NAME) {
            return new Driver($value);
        }
        else {
            return $value;
        }
    }
}