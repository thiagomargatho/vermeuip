<?php

declare(strict_types=1);

namespace Vermeuip;

final class PropagationEndpoint
{
    /**
     * @param array<string,mixed> $query
     * @param null|callable(string):?string $http
     * @return array{0:int,1:array<string,mixed>}
     */
    public static function handle(array $query, string $clientIp, RateLimiter $limiter, ?callable $http = null): array
    {
        if (!$limiter->hit('prop|' . $clientIp)) {
            return [429, ['erro' => 'Muitas consultas. Aguarde um minuto.']];
        }

        $domain = Guard::normalizeDomain((string) ($query['dominio'] ?? ''));
        if ($domain === null) {
            return [400, ['erro' => 'Domínio inválido.']];
        }

        $type = strtoupper((string) ($query['tipo'] ?? 'A'));
        if (!DnsPropagation::supports($type)) {
            return [400, ['erro' => 'Tipo de registro inválido.']];
        }

        $resultados = (new DnsPropagation($http))->query($domain, $type);

        return [200, [
            'dominio' => $domain,
            'tipo' => $type,
            'consistente' => DnsPropagation::consistent($resultados),
            'resultados' => $resultados,
        ]];
    }
}
