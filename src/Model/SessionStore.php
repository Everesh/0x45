<?php

declare(strict_types=1);

namespace Everesh\ZeroX45\Model;

class SessionStore
{
    private const USER = "user";

    public function login(
        int $id,
        string $username,
        bool $super = false,
    ): void {
        session_regenerate_id(true);
        $_SESSION[self::USER] = [
            "id" => $id,
            "username" => $username,
            "super" => $super,
        ];
    }

    public function logout(): void
    {
        unset($_SESSION[self::USER]);
        session_regenerate_id(true);
    }

    public function user(): ?array
    {
        return $_SESSION[self::USER] ?? null;
    }

    public function isLoggedIn(): bool
    {
        return $this->user() !== null;
    }

    public function isSuper(): bool
    {
        return (bool) ($this->user()["super"] ?? false);
    }

    public function key(): string
    {
        $user = $this->user();

        return $user ? "u:" . $user["id"] : "s:" . session_id();
    }

    public function username(): string
    {
        $user = $this->user();

        return $user !== null
            ? $user["username"]
            : hash("sha256", session_id() . $_ENV["SESSION_SALT"]);
    }
}
