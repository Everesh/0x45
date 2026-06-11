<?php

declare(strict_types=1);

namespace Everesh\ZeroX45\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;
use Slim\Views\PhpRenderer;

class AuthController
{
    public function __construct(
        private readonly PhpRenderer $view,
        private readonly Connection $db,
    ) {}

    public function loginPage(Request $request, Response $response): Response
    {
        if ($request->getAttribute("session")->isLoggedIn()) {
            return $this->redirectHome($request, $response);
        }

        return $this->view->render($response, "login.php");
    }

    public function login(Request $request, Response $response): Response
    {
        [$username, $passwd] = $this->credentials($request);

        $user =
            $username === ""
                ? false
                : $this->db->fetchAssociative(
                    "SELECT id, username, passwd, super
                     FROM user WHERE username = ?",
                    [$username],
                );

        if (!$user || !password_verify($passwd, $user["passwd"])) {
            return $this->view
                ->render($response, "login.php", [
                    "error" => "invalid credentials",
                    "username" => $username,
                ])
                ->withStatus(401);
        }

        $request
            ->getAttribute("session")
            ->login(
                (int) $user["id"],
                $user["username"],
                (bool) $user["super"],
            );

        return $this->redirectHome($request, $response);
    }

    public function registerPage(
        Request $request,
        Response $response,
    ): Response {
        if ($request->getAttribute("session")->isLoggedIn()) {
            return $this->redirectHome($request, $response);
        }

        return $this->view->render($response, "register.php");
    }

    public function register(Request $request, Response $response): Response
    {
        [$username, $passwd] = $this->credentials($request);

        if ($username === "" || $passwd === "") {
            return $this->view
                ->render($response, "register.php", [
                    "error" => "username and password are required",
                    "username" => $username,
                ])
                ->withStatus(400);
        }

        try {
            $this->db->insert("user", [
                "username" => $username,
                "passwd" => password_hash($passwd, PASSWORD_DEFAULT),
            ]);
        } catch (UniqueConstraintViolationException) {
            return $this->view
                ->render($response, "register.php", [
                    "error" => "username is already taken",
                    "username" => $username,
                ])
                ->withStatus(409);
        }

        $request
            ->getAttribute("session")
            ->login((int) $this->db->lastInsertId(), $username);

        return $this->redirectHome($request, $response);
    }

    public function logout(Request $request, Response $response): Response
    {
        $request->getAttribute("session")->logout();

        return $this->redirectHome($request, $response);
    }

    /**
     * @return array{string, string} trimmed username + raw password
     */
    private function credentials(Request $request): array
    {
        $body = (array) $request->getParsedBody();

        return [
            trim((string) ($body["username"] ?? "")),
            (string) ($body["passwd"] ?? ""),
        ];
    }

    private function redirectHome(
        Request $request,
        Response $response,
    ): Response {
        $basePath = RouteContext::fromRequest($request)->getBasePath();

        return $response
            ->withHeader("Location", $basePath . "/")
            ->withStatus(302);
    }
}
