<?php

declare(strict_types=1);

session_start();

use App\Application;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application();
$app->run();