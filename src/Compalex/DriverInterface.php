<?php

declare(strict_types=1);

namespace Compalex;

interface DriverInterface
{
    function getAdapterName() : string;
    function getCompareTables() : array;
    function getAdditionalTableInfo() : array;
    function getCompareIndex() : array;
    function getCompareProcedures() : array;
    function getCompareFunctions() : array;
    function getCompareViews() : array;
    function getCompareKeys() : array;
    function getCompareTriggers() : array;
    function getTableRows(string $baseName, string $tableName, int $rowCount) : array;
}