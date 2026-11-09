<?php

declare(strict_types=1);

namespace Vermeuip;

/**
 * Compara o IP WAN que o roteador do visitante mostra com o IP que a internet enxerga.
 * O navegador nao consegue ler o IP WAN do roteador, entao o visitante informa.
 */
final class Cgnat
{
    /** @return array{veredito:string,mensagem:string}|null null = IP WAN invalido */
    public static function classify(string $wan, string $publicIp): ?array
    {
        $wan = trim($wan);

        if (filter_var($wan, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
            return self::out('ipv6', 'CGNAT só existe em IPv4. Informe o endereço IPv4 da WAN do roteador.');
        }
        if (filter_var($wan, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            return null;
        }

        if (self::inRange($wan, '100.64.0.0', 10)) {
            return self::out('cgnat', 'Você está em CGNAT. O IP WAN do roteador está na faixa 100.64.0.0/10, '
                . 'reservada para o NAT dos provedores. Não é possível abrir portas de fora; peça um IP público ao provedor.');
        }
        if (self::inRange($wan, '10.0.0.0', 8) || self::inRange($wan, '172.16.0.0', 12) || self::inRange($wan, '192.168.0.0', 16)) {
            return self::out('nat_duplo', 'O IP WAN do roteador é privado. Ou o roteador está atrás de outro equipamento '
                . '(por exemplo, o modem do provedor em modo roteador), ou o provedor usa CGNAT com faixa privada. '
                . 'Nos dois casos, portas abertas no roteador não ficam acessíveis de fora.');
        }
        if (!Guard::isPublicIp($wan)) {
            return null;
        }

        if (filter_var($publicIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            return self::out('sem_comparacao', 'O IP WAN informado é público, o que é um bom sinal. '
                . 'Mas você acessou esta página por IPv6, então não dá para comparar com o seu IPv4 visto na internet.');
        }
        if ($wan === $publicIp) {
            return self::out('sem_cgnat', 'Você não está em CGNAT. O IP WAN do roteador é o mesmo que a internet enxerga, '
                . 'então dá para abrir portas no roteador.');
        }

        return self::out('diferente', 'O IP WAN do roteador é público, mas diferente do que a internet enxerga. '
            . 'Isso acontece com VPN ou proxy ligados, ou quando o provedor faz CGNAT usando IPs públicos. '
            . 'Desligue a VPN e teste de novo.');
    }

    private static function inRange(string $ip, string $base, int $bits): bool
    {
        $mask = -1 << (32 - $bits);
        return (ip2long($ip) & $mask) === (ip2long($base) & $mask);
    }

    /** @return array{veredito:string,mensagem:string} */
    private static function out(string $veredito, string $mensagem): array
    {
        return ['veredito' => $veredito, 'mensagem' => $mensagem];
    }
}
