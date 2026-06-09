<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Everesh\ZeroX45\Controller\HomeController;
use Everesh\ZeroX45\Controller\PostController;
use Everesh\ZeroX45\Middleware\SessionMiddleware;
use Everesh\ZeroX45\Model\Database;

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;

require __DIR__ . "/../vendor/autoload.php";

Dotenv::createImmutable(__DIR__ . "/../")->load();

$app = AppFactory::create();
$basePath = rtrim(
    preg_replace('#/public$#i', "", dirname($_SERVER["SCRIPT_NAME"])),
    "/",
);
$app->setBasePath($basePath);
$view = new PhpRenderer(__DIR__ . "/../src/View", ["basePath" => $basePath]);
$db = new Database()->get();

$app->add(new SessionMiddleware());
$dev = ($_ENV["APP_ENV"] ?? "prod") === "dev";
$app->addErrorMiddleware($dev, true, $dev);

$app->get("/", [new HomeController($view, $db), "index"]);
$app->get("/post/{id}", [new PostController($view, $db), "show"]);

$app->run();
