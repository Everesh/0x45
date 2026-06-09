<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Everesh\ZeroX45\Controller\HomeController;
use Everesh\ZeroX45\Middleware\SessionMiddleware;
use Everesh\ZeroX45\Model\Database;

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;

require __DIR__ . "/../vendor/autoload.php";

Dotenv::createImmutable(__DIR__ . "/../")->load();

$app = AppFactory::create();
$view = new PhpRenderer(__DIR__ . "/../src/View");
$db = new Database();

$app->add(new SessionMiddleware());
$dev = ($_ENV["APP_ENV"] ?? "prod") === "dev";
$app->addErrorMiddleware($dev, true, $dev);

$app->get("/", [new HomeController($view), "index"]);

$app->run();
