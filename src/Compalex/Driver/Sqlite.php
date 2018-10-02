<?php

namespace Compalex\Driver;

use Compalex\Driver;

class Sqlite extends Driver
{
    public function getAdapterName(): string
    {
        return 'sqlite';
    }
}