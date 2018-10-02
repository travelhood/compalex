<?php

declare(strict_types=1);

namespace Compalex\Driver;

use Compalex\Driver;

class Pgsql extends Driver
{
    public function getAdapterName(): string
    {
        return 'pgsql';
    }
}