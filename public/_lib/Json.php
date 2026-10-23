<?php

declare(strict_types=1);

namespace Vermeuip;

final class Json
{
    /** @param array<mixed> $body */
    public static function encode(array $body): string
    {
        // Alguns php.ini (ex.: o do cPanel do dono) usam serialize_precision=17, que imprime
        // -21.3594 como -21.359400000000001. -1 usa a representacao mais curta que volta ao mesmo float.
        $previous = ini_set('serialize_precision', '-1');
        try {
            return json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } finally {
            ini_set('serialize_precision', (string) $previous);
        }
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
