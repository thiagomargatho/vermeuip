<?php

declare(strict_types=1);

namespace Vermeuip;

final class WhoisEndpoint
{
    /**
     * @param array<string,mixed> $query
     * @param null|callable(string):?array<string,mixed> $http
     * @return array{0:int,1:array<string,mixed>}
     */
    public static function handle(array $query, string $clientIp, RateLimiter $limiter, ?callable $http = null): array
    {
        if (!$limiter->hit('whois|' . $clientIp)) {
            return [429, ['erro' => 'Muitas consultas. Aguarde um minuto.']];
        }

        $domain = Guard::normalizeDomain((string) ($query['dominio'] ?? ''));
        if ($domain === null) {
            return [400, ['erro' => 'Domínio inválido.']];
        }

        try {
            return [200, Rdap::lookup($domain, $http ?? [Rdap::class, 'curlGet'])];
        } catch (DomainNotFound) {
            return [404, ['erro' => 'Domínio não encontrado.']];
        } catch (\RuntimeException) {
            return [502, ['erro' => 'Serviço de consulta indisponível. Tente novamente.']];
        }
    }
}
