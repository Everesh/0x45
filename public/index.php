<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Everesh\ZeroX45\Controller\AuthController;
use Everesh\ZeroX45\Controller\HomeController;
use Everesh\ZeroX45\Controller\PostController;
use Everesh\ZeroX45\Middleware\SessionMiddleware;
use Everesh\ZeroX45\Model\Database;
use Everesh\ZeroX45\Model\SessionStore;
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
$db = (new Database())->get();

$session = new SessionStore();
// attribute makes it visible in every template, same as basePath
$view->addAttribute("session", $session);

$app->add(new SessionMiddleware($session));
$dev = ($_ENV["APP_ENV"] ?? "prod") === "dev";
$app->addErrorMiddleware($dev, true, $dev);

$app->get("/", [new HomeController($view, $db), "index"]);
$app->get("/login", [new AuthController($view, $db), "loginPage"]);
$app->post("/login", [new AuthController($view, $db), "login"]);
$app->get("/register", [new AuthController($view, $db), "registerPage"]);
$app->post("/register", [new AuthController($view, $db), "register"]);
$app->post("/logout", [new AuthController($view, $db), "logout"]);
$app->get("/post/{id}", [new PostController($view, $db), "show"]);
$app->post("/post/{id}/endorse", [new PostController($view, $db), "endorse"]);
$app->get(
    "/post/{id}/endorse",
    fn ($request, $response) => $view
        ->render($response, "405.php")
        ->withStatus(405),
);

$app->run();
