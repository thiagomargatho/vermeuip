<?php

declare(strict_types=1);

namespace Vermeuip;

final class Ptr
{
    /** @param null|callable(string):(string|false) $resolver */
    public static function lookup(string $ip, ?callable $resolver = null): ?string
    {
        if (!Guard::isPublicIp($ip)) {
            return null;
        }

        $resolver ??= static fn (string $i): string|false => gethostbyaddr($i);
        $host = $resolver($ip);

        if (!is_string($host) || $host === '' || $host === $ip) {
            return null;
        }

        return $host;
    }
}
