<?php

declare(strict_types=1);

use Everesh\ZeroX45\Middleware\SessionMiddleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
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

$app->get("/", function (Request $request, Response $response) use ($view) {
    return $view->render($response, "home.php", [
        "sessionId" => session_id(),
    ]);
});

$app->run();
