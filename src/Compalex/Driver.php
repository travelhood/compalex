<?php

declare(strict_types=1);

namespace Compalex;

use Compalex\Driver\Mysql;
use PDO;

/**
 * @property-read Config\Driver $config
 */
abstract class Driver implements DriverInterface
{
    const PDO_OPTIONS = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    /** @var Config\Driver */
    protected $_config;

    /** @var PDO */
    protected $_connection;

    abstract public function getAdapterName() : string;

    public static function factory(Config\Driver $config)
    {
        switch($config->driver) {
            default:
                throw new Exception('Unsupported driver: '.$config->driver);
            case 'mysql':
                return new Mysql($config);
        }
    }

    protected function _select(string $query, PDO $connect, string $baseName) : array
    {
        $out = [];
        $query = str_replace('<<BASENAME>>', $baseName, $query);
        $stmt = $connect->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $out[] = $row;
        }
        return $out;
    }


    protected function _getCompareArray(string $query, bool $diffMode = false, bool $ifOneLevelDiff = false) : array
    {

        $out = [];
        $fArray = $this->_prepareOutArray($this->_select($query, $this->_getFirstConnect(), FIRST_BASE_NAME), $diffMode, $ifOneLevelDiff);
        $sArray = $this->_prepareOutArray($this->_select($query, $this->_getSecondConnect(), SECOND_BASE_NAME), $diffMode, $ifOneLevelDiff);

        $allTables = array_unique(array_merge(array_keys($fArray), array_keys($sArray)));
        sort($allTables);

        foreach ($allTables as $v) {
            $allFields = array_unique(array_merge(array_keys((array)@$fArray[$v]), array_keys((array)@$sArray[$v])));
            foreach ($allFields as $f) {
                switch (true) {
                    case (!isset($fArray[$v][$f])): {
                        if(is_array($sArray[$v][$f])) $sArray[$v][$f]['isNew'] = true;
                        break;
                    }
                    case (!isset($sArray[$v][$f])): {
                        if(is_array($fArray[$v][$f])) $fArray[$v][$f]['isNew'] = true;
                        break;
                    }
                    case (isset($fArray[$v][$f]['dtype']) && isset($sArray[$v][$f]['dtype']) && ($fArray[$v][$f]['dtype'] != $sArray[$v][$f]['dtype'])) : {
                        $fArray[$v][$f]['changeType'] = true;
                        $sArray[$v][$f]['changeType'] = true;
                        break;
                    }
                }
            }
            $out[$v] = [
                'fArray' => @$fArray[$v],
                'sArray' => @$sArray[$v],
            ];
        }
        return $out;
    }

    private function _prepareOutArray(array $result, bool $diffMode, bool $ifOneLevelDiff) : array
    {
        $mArray = [];
        foreach ($result as $r) {
            if ($diffMode) {
                foreach (explode("\n", $r['ARRAY_KEY_2']) as $pr) {
                    $mArray[$r['ARRAY_KEY_1']][$pr] = $r;
                }

            } else {
                if($ifOneLevelDiff){
                    $mArray[$r['ARRAY_KEY_1']] = $r;
                }else{
                    $mArray[$r['ARRAY_KEY_1']][$r['ARRAY_KEY_2']] = $r;
                }
            }
        }
        return $mArray;
    }

    public function __construct(Config\Driver $config)
    {
        $this->_config = $config;
        $this->_connection = new PDO($this->config->buildDsn(), $this->config->username, $this->config->password, self::PDO_OPTIONS);
    }

    public function __get($name)
    {
        switch($name) {
            case 'config':
                return $this->_config;
        }
        return null;
    }

    public function getCompareTables() : array
    {
        throw new Exception(__METHOD__ . ' Not work');
    }

    public function getAdditionalTableInfo() : array
    {
        return [];
    }

    public function getCompareIndex() : array
    {
        throw new Exception(__METHOD__ . ' Not work');
    }

    public function getCompareProcedures() : array
    {
        throw new Exception(__METHOD__ . ' Not work');
    }

    public function getCompareFunctions() : array
    {
        throw new Exception(__METHOD__ . ' Not work');
    }

    public function getCompareViews() : array
    {
        throw new Exception(__METHOD__ . ' Not work');
    }

    public function getCompareKeys() : array
    {
        throw new Exception(__METHOD__ . ' Not work');
    }

    public function getCompareTriggers() : array
    {
        throw new Exception(__METHOD__ . ' Not work');
    }

    public function getTableRows(string $baseName, string $tableName, int $rowCount = SAMPLE_DATA_LENGTH) : array
    {
        if (!$baseName) throw new Exception('$baseName is not set');
        if (!$tableName) throw new Exception('$tableName is not set');
        $rowCount = (int)$rowCount;
        $tableName = preg_replace("$[^A-z0-9.,-_]$", '', $tableName);
        switch (DRIVER) {
            case "mssql":
            case "dblib":
                $query = 'SELECT TOP ' . $rowCount . ' * FROM ' . $baseName . '..' . $tableName;
                break;
            case "pgsql":
            case "mysql":
                $query = 'SELECT * FROM ' . $tableName . ' LIMIT ' . $rowCount;
                break;

        }
        if ($baseName === FIRST_BASE_NAME) {
            $result = $this->_select($query, $this->_getFirstConnect(), FIRST_BASE_NAME);
        } else {
            $result = $this->_select($query, $this->_getSecondConnect(), SECOND_BASE_NAME);
        }

        if ($result) {
            $firstRow = array_shift($result);

            $out[] = array_keys($firstRow);
            $out[] = array_values($firstRow);

            foreach ($result as $row) {
                $out[] = array_values($row);
            }
        } else {
            $out = [];
        }

        if (DATABASE_ENCODING != 'utf-8' && $out) {
            // $out = array_map(function($item){ return array_map(function($itm){ return iconv(DATABASE_ENCODING, 'utf-8', $itm); }, $item); }, $out);
        }

        return $out;
    }
}
