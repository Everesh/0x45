<?php

declare(strict_types=1);

namespace Everesh\ZeroX45\Middleware;

use Everesh\ZeroX45\Model\SessionStore;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class SessionMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly SessionStore $store) {}

    public function process(Request $request, Handler $handler): Response
    {
        // idempotency guard
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $handler->handle($request->withAttribute("session", $this->store));
    }
}
