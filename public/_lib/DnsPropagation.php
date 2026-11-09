<?php

declare(strict_types=1);

namespace Vermeuip;

/**
 * Consulta o mesmo registro em varios resolvedores publicos (DNS sobre HTTPS, resposta em JSON)
 * para ver se todos ja respondem igual.
 */
final class DnsPropagation
{
    private const TYPES = ['A' => 1, 'NS' => 2, 'CNAME' => 5, 'MX' => 15, 'TXT' => 16, 'AAAA' => 28];

    public const RESOLVERS = [
        'Google' => 'https://dns.google/resolve',
        'Cloudflare' => 'https://cloudflare-dns.com/dns-query',
        'AdGuard' => 'https://dns.adguard-dns.com/resolve',
        'DNS.SB' => 'https://doh.dns.sb/dns-query',
    ];

    /** @var callable(string):?string */
    private $http;

    /** @param null|callable(string):?string $http devolve o corpo da resposta, ou null em falha */
    public function __construct(?callable $http = null)
    {
        $this->http = $http ?? [self::class, 'httpGet'];
    }

    public static function supports(string $type): bool
    {
        return isset(self::TYPES[strtoupper($type)]);
    }

    /** @return list<array{resolvedor:string,ok:bool,valores:list<string>,ttl:?int}> */
    public function query(string $domain, string $type): array
    {
        $type = strtoupper($type);
        if (!isset(self::TYPES[$type])) {
            throw new \InvalidArgumentException('Tipo de registro inválido.');
        }

        $out = [];
        foreach (self::RESOLVERS as $nome => $url) {
            $corpo = ($this->http)($url . '?' . http_build_query(['name' => $domain, 'type' => $type]));
            $out[] = ['resolvedor' => $nome] + self::parse($corpo, self::TYPES[$type]);
        }
        return $out;
    }

    /**
     * Todos os resolvedores que responderam devolveram os mesmos valores?
     *
     * @param list<array{resolvedor:string,ok:bool,valores:list<string>,ttl:?int}> $resultados
     */
    public static function consistent(array $resultados): bool
    {
        $conjuntos = [];
        foreach ($resultados as $r) {
            if ($r['ok']) {
                $conjuntos[implode("\n", $r['valores'])] = true;
            }
        }
        return count($conjuntos) === 1;
    }

    /** @return array{ok:bool,valores:list<string>,ttl:?int} */
    private static function parse(?string $corpo, int $codigo): array
    {
        $dados = $corpo === null ? null : json_decode($corpo, true);
        if (!is_array($dados) || !isset($dados['Status'])) {
            return ['ok' => false, 'valores' => [], 'ttl' => null];
        }

        $valores = [];
        $ttl = null;
        foreach ((array) ($dados['Answer'] ?? []) as $r) {
            if (!is_array($r) || (int) ($r['type'] ?? 0) !== $codigo) {
                continue;
            }
            $valor = (string) ($r['data'] ?? '');
            if ($codigo === self::TYPES['TXT']) {
                // "parte1" "parte2" -> parte1parte2
                $valor = (string) preg_replace('/^"|"$|"\s+"/', '', $valor);
            } else {
                $valor = strtolower(rtrim($valor, '.'));
            }
            $valores[] = $valor;
            $ttl = isset($r['TTL']) ? (int) $r['TTL'] : $ttl;
        }
        sort($valores);

        return ['ok' => true, 'valores' => $valores, 'ttl' => $ttl];
    }

    /** GET por streams (o host nao tem a extensao curl): so https, timeout curto. */
    public static function httpGet(string $url): ?string
    {
        if (!str_starts_with($url, 'https://')) {
            return null;
        }
        $contexto = stream_context_create(['http' => [
            'method' => 'GET',
            'timeout' => 3,
            'ignore_errors' => true,
            // "Connection: close" evita esperar o timeout quando o servidor mantem a conexao aberta
            'header' => "Accept: application/dns-json\r\nUser-Agent: vermeuip.com.br\r\nConnection: close\r\n",
        ]]);
        $corpo = @file_get_contents($url, false, $contexto);
        if ($corpo === false || Rdap::statusFromHeaders($http_response_header ?? []) !== 200) {
            return null;
        }
        return $corpo;
    }
}
