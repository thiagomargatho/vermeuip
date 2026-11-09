<?php

declare(strict_types=1);

namespace Vermeuip;

final class CgnatEndpoint
{
    /**
     * @param array<string,mixed> $query
     * @return array{0:int,1:array<string,mixed>}
     */
    public static function handle(array $query, string $clientIp, RateLimiter $limiter): array
    {
        if (!$limiter->hit('cgnat|' . $clientIp)) {
            return [429, ['erro' => 'Muitas consultas. Aguarde um minuto.']];
        }

        $wan = trim((string) ($query['wan'] ?? ''));
        $resultado = Cgnat::classify($wan, $clientIp);
        if ($resultado === null) {
            return [400, ['erro' => 'IP WAN inválido. Digite o endereço IPv4 que aparece no roteador, por exemplo 100.72.10.5.']];
        }

        return [200, ['wan' => $wan, 'ip_publico' => $clientIp] + $resultado];
    }
}
