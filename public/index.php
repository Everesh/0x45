<?php

declare(strict_types=1);

use Everesh\ZeroX45\Controller\HomeController;
use Everesh\ZeroX45\Middleware\SessionMiddleware;

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;

require __DIR__ . "/../vendor/autoload.php";

$app = AppFactory::create();

$view = new PhpRenderer(__DIR__ . "/../src/View");

$app->add(new SessionMiddleware());

/**
 * @param bool                  $displayErrorDetails -> Should be set to false in production
 * @param bool                  $logErrors -> Parameter is passed to the default ErrorHandler
 * @param bool                  $logErrorDetails -> Display error details in error log
 * @param LoggerInterface|null  $logger -> Optional PSR-3 Logger
 */
$app->addErrorMiddleware(true, true, true);

$app->get("/", [new HomeController($view), "index"]);

$app->run();
