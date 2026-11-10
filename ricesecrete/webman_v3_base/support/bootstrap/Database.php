<?php

declare(strict_types=1);

namespace support\bootstrap;

use Illuminate\Database\Capsule\Manager as Capsule;
use Webman\Bootstrap;

class Database implements Bootstrap
{
    public static function start($worker): void
    {
        $config = config('database');
        $capsule = new Capsule();
        $capsule->addConnection($config['connections'][$config['default']]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}
