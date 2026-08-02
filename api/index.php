<?php

declare(strict_types=1);

use App\Core\Router;
use App\Core\Request;

require __DIR__ . '/../includes/bootstrap.php';

$router = new Router(new Request());
require __DIR__ . '/../routes/api.php';

$router->dispatch();
