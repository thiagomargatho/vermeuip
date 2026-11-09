<?php

declare(strict_types=1);

namespace Vermeuip;

/** Localizacao aproximada, provedor e IP reverso de um IP publico qualquer. */
final class IpLookupEndpoint
{
    /**
     * @param array<string,mixed> $query
     * @param null|callable(string):(string|false) $ptrResolver
     * @return array{0:int,1:array<string,mixed>}
     */
    public static function handle(array $query, string $clientIp, RateLimiter $limiter, ?callable $ptrResolver = null): array
    {
        if (!$limiter->hit('ip|' . $clientIp)) {
            return [429, ['erro' => 'Muitas consultas. Aguarde um minuto.']];
        }

        $ip = trim((string) ($query['ip'] ?? ''));
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return [400, ['erro' => 'IP inválido. Digite um endereço IPv4 ou IPv6, por exemplo 8.8.8.8.']];
        }
        if (!Guard::isPublicIp($ip)) {
            return [400, ['erro' => 'Esse IP é privado ou reservado e não tem localização na internet.']];
        }

        $config = Config::get();
        $geo = (new Geo($config['GEO_CITY_DB'], $config['GEO_ASN_DB']))->lookup($ip);

        return [200, ['ip' => $ip, 'ptr' => Ptr::lookup($ip, $ptrResolver)] + $geo];
    }
}
