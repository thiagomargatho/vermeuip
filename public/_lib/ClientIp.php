<?php

declare(strict_types=1);

namespace Vermeuip;

final class ClientIp
{
    /**
     * REMOTE_ADDR, exceto quando ele for um proxy confiavel: ai usa o
     * X-Forwarded-For, da direita para a esquerda, pulando proxies confiaveis.
     *
     * @param array<string,mixed> $server
     * @param string[]            $trustedProxies
     */
    public static function resolve(array $server, array $trustedProxies = []): string
    {
        $remote = (string) ($server['REMOTE_ADDR'] ?? '');

        if ($remote === '' || !in_array($remote, $trustedProxies, true)) {
            return $remote;
        }

        $chain = array_reverse(array_map('trim', explode(',', (string) ($server['HTTP_X_FORWARDED_FOR'] ?? ''))));
        foreach ($chain as $candidate) {
            if (filter_var($candidate, FILTER_VALIDATE_IP) === false) {
                return $remote;
            }
            if (!in_array($candidate, $trustedProxies, true)) {
                return $candidate;
            }
        }

        return $remote;
    }
}
