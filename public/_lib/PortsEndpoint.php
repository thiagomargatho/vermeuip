<?php

declare(strict_types=1);

namespace Vermeuip;

final class PortsEndpoint
{
    /**
     * O IP testado e sempre o do proprio visitante; qualquer "ip"/"host" no corpo e ignorado.
     *
     * @param array<string,mixed> $body
     * @param null|callable(string,float):bool $connector
     * @return array{0:int,1:array<string,mixed>}
     */
    public static function handle(array $body, string $clientIp, RateLimiter $limiter, ?callable $connector = null): array
    {
        if (!$limiter->hit('ports|' . $clientIp)) {
            return [429, ['erro' => 'Muitas consultas. Aguarde um minuto.']];
        }

        if (!Guard::isPublicIp($clientIp)) {
            return [400, ['erro' => 'Seu IP não é público, então não dá para testar portas.']];
        }

        $port = Guard::parsePort($body['porta'] ?? null);
        if ($port === null) {
            return [400, ['erro' => 'Porta inválida. Use um número de 1 a 65535.']];
        }

        try {
            $estado = PortProbe::check($clientIp, $port, 3.0, $connector);
        } catch (OutboundBlocked) {
            return [503, ['erro' => 'Este servidor não permite testes de porta de saída.']];
        }

        return [200, ['ip' => $clientIp, 'porta' => $port, 'estado' => $estado]];
    }
}
