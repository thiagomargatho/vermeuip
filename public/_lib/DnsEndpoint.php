<?php

declare(strict_types=1);

namespace Vermeuip;

final class DnsEndpoint
{
    /**
     * @param array<string,mixed> $query
     * @param null|callable(string,int):(array<int,array<string,mixed>>|false) $resolver
     * @return array{0:int,1:array<string,mixed>}
     */
    public static function handle(array $query, string $clientIp, RateLimiter $limiter, ?callable $resolver = null): array
    {
        if (!$limiter->hit('dns|' . $clientIp)) {
            return [429, ['erro' => 'Muitas consultas. Aguarde um minuto.']];
        }

        $domain = Guard::normalizeDomain((string) ($query['dominio'] ?? ''));
        if ($domain === null) {
            return [400, ['erro' => 'Domínio inválido.']];
        }

        $type = strtoupper((string) ($query['tipo'] ?? 'A'));
        if (!DnsLookup::supports($type)) {
            return [400, ['erro' => 'Tipo de registro inválido.']];
        }

        return [200, ['registros' => (new DnsLookup($resolver))->query($domain, $type)]];
    }
}
