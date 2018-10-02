<?php

declare(strict_types=1);

namespace Compalex\Driver;

use Compalex\Driver;

class Mysql extends Driver
{
    public function getAdapterName(): string
    {
        return 'mysql';
    }

    public function getCompareTables() : array
    {
        return $this->_getTableAndViewResult('BASE TABLE');
    }

    public function getAdditionalTableInfo() : array
    {
        $type = 'BASE TABLE';
        $query = "SELECT
                TABLE_NAME ARRAY_KEY_1,
                ENGINE engine,
                TABLE_COLLATION collation
            FROM
                information_schema.TABLES
            WHERE
                TABLE_SCHEMA = '{$this->config->database}' AND
                TABLE_TYPE = '{$type}'";
        return $this->_getCompareArray($query, false, true);

    }

    public function getCompareViews() : array
    {
        // show view definition
        /*
        $query = "SELECT
                    TABLE_NAME ARRAY_KEY_1,
                    VIEW_DEFINITION ARRAY_KEY_2,
                    '' dtype
                  FROM
                    information_schema.VIEWS
                  WHERE
                    TABLE_SCHEMA = '{$this->config->database}'
                  ORDER BY
                    TABLE_NAME";
        $data =  $this->_getCompareArray($query, true);
        return $data;
        */
        // show only fields and it types
        return $this->_getTableAndViewResult('VIEW');
    }

    public function getCompareProcedures() : array
    {
        return $this->_getRoutineResult('PROCEDURE');
    }

    public function getCompareFunctions() : array
    {
        return $this->_getRoutineResult('FUNCTION');
    }

    public function getCompareKeys() : array
    {
        $query = "SELECT
                    CONCAT(TABLE_NAME, ' [', INDEX_NAME, '] ') ARRAY_KEY_1,
                    COLUMN_NAME  ARRAY_KEY_2,
                    CONCAT('(' , SEQ_IN_INDEX, ')') dtype
                  FROM INFORMATION_SCHEMA.STATISTICS
                  WHERE
                    TABLE_SCHEMA = '{$this->config->database}'
                  ORDER BY
                    TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX";
        return $this->_getCompareArray($query);
    }

    public function getCompareTriggers() : array
    {
        $query = "SELECT
                    CONCAT(EVENT_OBJECT_TABLE, '::' , TRIGGER_NAME, ' [', EVENT_MANIPULATION, '/',  ACTION_ORIENTATION, '/', ACTION_TIMING, '] - ', ACTION_ORDER) ARRAY_KEY_1,
                    ACTION_STATEMENT ARRAY_KEY_2,
                    '' dtype
                  FROM
                    information_schema.TRIGGERS
                  WHERE
                    TRIGGER_SCHEMA = '{$this->config->database}'";
        return $this->_getCompareArray($query);
    }

    private function _getTableAndViewResult(string $type) : array
    {
        $query = "SELECT
                    cl.TABLE_NAME ARRAY_KEY_1,
                    cl.COLUMN_NAME ARRAY_KEY_2,
                    cl.COLUMN_TYPE dtype
                  FROM information_schema.columns cl,  information_schema.TABLES ss
                  WHERE
                    cl.TABLE_NAME = ss.TABLE_NAME AND
                    cl.TABLE_SCHEMA = '{$this->config->database}' AND
                    ss.TABLE_TYPE = '{$type}'
                  ORDER BY
                    cl.table_name ";
        return $this->_getCompareArray($query);
    }

    private function _getRoutineResult(string $type) : array
    {
        $query = "SELECT
                    ROUTINE_NAME ARRAY_KEY_1,
                    ROUTINE_DEFINITION ARRAY_KEY_2,
                    '' dtype
                  FROM
                    information_schema.ROUTINES
                  WHERE
                    ROUTINE_SCHEMA = '{$this->config->database}' AND
                    ROUTINE_TYPE = '{$type}'
                  ORDER BY
                    ROUTINE_NAME";
        return $this->_getCompareArray($query, true);
    }
}