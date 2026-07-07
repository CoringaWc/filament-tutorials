<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../../vendor/autoload.php';

if (! defined('TESTBENCH_WORKING_PATH') && is_string($workingPath = getenv('TESTBENCH_WORKING_PATH'))) {
    define('TESTBENCH_WORKING_PATH', $workingPath);
}

/** @var Application $app */
$app = require_once __DIR__.'/../../vendor/orchestra/testbench-core/laravel/bootstrap/app.php';

$app->handleRequest(Request::capture());
