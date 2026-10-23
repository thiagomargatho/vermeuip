<?php

declare(strict_types=1);

namespace Vermeuip;

final class Json
{
    /** @param array<mixed> $body */
    public static function encode(array $body): string
    {
        return json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    /** @param array<mixed> $body */
    public static function send(int $status, array $body): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo self::encode($body);
        exit;
    }
}
