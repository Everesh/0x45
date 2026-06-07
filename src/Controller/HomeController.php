<?php

declare(strict_types=1);

namespace Everesh\ZeroX45\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\PhpRenderer;

class HomeController
{
    public function __construct(private readonly PhpRenderer $view)
    {
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'home.php', [
            'sessionId' => session_id(),
        ]);
    }
}
