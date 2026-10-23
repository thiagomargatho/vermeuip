<?php

declare(strict_types=1);

namespace Vermeuip;

final class Guard
{
    public static function isPublicIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }

    /** Devolve o dominio em minusculas/punycode, ou null se invalido, IP literal ou localhost. */
    public static function normalizeDomain(string $input): ?string
    {
        $host = trim($input);
        $host = preg_replace('#^[a-z][a-z0-9+.\-]*://#i', '', $host) ?? '';
        $host = preg_split('#[/?\#]#', $host, 2)[0];
        $host = preg_replace('#^.*@#', '', $host) ?? '';
        $host = preg_replace('#:\d+$#', '', $host) ?? '';
        $host = strtolower(rtrim($host, '.'));

        if ($host === '' || $host === 'localhost') {
            return null;
        }

        $ascii = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
        if ($ascii === false || $ascii === '') {
            return null;
        }
        $ascii = strtolower($ascii);

        if (filter_var($ascii, FILTER_VALIDATE_IP) !== false) {
            return null;
        }
        if (strlen($ascii) > 253 || !str_contains($ascii, '.')) {
            return null;
        }
        if (!preg_match('/^([a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?\.)+[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?$/', $ascii)) {
            return null;
        }

        return $ascii;
    }

    public static function parsePort(mixed $input): ?int
    {
        if (is_string($input) && preg_match('/^\d{1,5}$/', $input)) {
            $input = (int) $input;
        }
        if (!is_int($input) || $input < 1 || $input > 65535) {
            return null;
        }
        return $input;
    }
}
