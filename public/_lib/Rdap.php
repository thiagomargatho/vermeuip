<?php

declare(strict_types=1);

namespace Vermeuip;

final class Rdap
{
    private const STATUS = ['active' => 'Ativo', 'ok' => 'Ativo', 'inactive' => 'Inativo',
        'pending delete' => 'Exclusão pendente', 'client hold' => 'Suspenso', 'server hold' => 'Suspenso'];

    /**
     * @param callable(string):?array<string,mixed> $http devolve o JSON decodificado, null se nao encontrado;
     *                                                   lanca RuntimeException em falha de rede
     * @return array{dominio:string,registrado_em:?string,expira_em:?string,status:list<string>,nameservers:list<string>,titular:?string}
     */
    public static function lookup(string $domain, callable $http): array
    {
        $base = str_ends_with($domain, '.br') ? 'https://rdap.registro.br/domain/' : 'https://rdap.org/domain/';
        $data = $http($base . $domain);
        if ($data === null) {
            throw new DomainNotFound($domain);
        }

        return [
            'dominio' => (string) ($data['ldhName'] ?? $domain),
            'registrado_em' => self::event($data, 'registration'),
            'expira_em' => self::event($data, 'expiration'),
            'status' => array_map(
                static fn ($s): string => self::STATUS[strtolower((string) $s)] ?? (string) $s,
                array_values($data['status'] ?? [])
            ),
            'nameservers' => array_values(array_filter(array_map(
                static fn ($n): string => (string) ($n['ldhName'] ?? ''),
                $data['nameservers'] ?? []
            ))),
            'titular' => self::registrant($data),
        ];
    }

    /**
     * GET por streams (sem depender da extensao curl): timeout curto, so https,
     * ate 3 redirecionamentos. null = 404.
     */
    public static function httpGet(string $url): ?array
    {
        if (!str_starts_with($url, 'https://')) {
            throw new \RuntimeException('Somente https');
        }

        $context = stream_context_create(['http' => [
            'method' => 'GET',
            'timeout' => 8,
            'follow_location' => 1,
            'max_redirects' => 3,
            'ignore_errors' => true,
            'header' => "Accept: application/rdap+json, application/json\r\nUser-Agent: vermeuip.com.br\r\n",
        ]]);

        $body = @file_get_contents($url, false, $context);
        $code = self::statusFromHeaders($http_response_header ?? []);

        if ($body === false || $code === 0) {
            throw new \RuntimeException('Falha de rede');
        }
        if ($code === 404) {
            return null;
        }
        $data = json_decode($body, true);
        if ($code >= 400 || !is_array($data)) {
            throw new \RuntimeException('RDAP HTTP ' . $code);
        }
        return $data;
    }

    /** Codigo da ultima linha de status ("HTTP/1.1 200 OK") nos cabecalhos, 0 se nao houver. */
    public static function statusFromHeaders(array $headers): int
    {
        $code = 0;
        foreach ($headers as $line) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', (string) $line, $m)) {
                $code = (int) $m[1];
            }
        }
        return $code;
    }

    /** @param array<string,mixed> $data */
    private static function event(array $data, string $action): ?string
    {
        foreach ($data['events'] ?? [] as $e) {
            if (($e['eventAction'] ?? '') === $action && isset($e['eventDate'])) {
                $ts = strtotime((string) $e['eventDate']);
                return $ts === false ? null : gmdate('d/m/Y', $ts);
            }
        }
        return null;
    }

    /** @param array<string,mixed> $data */
    private static function registrant(array $data): ?string
    {
        foreach ($data['entities'] ?? [] as $entity) {
            if (!in_array('registrant', $entity['roles'] ?? [], true)) {
                continue;
            }
            foreach ($entity['vcardArray'][1] ?? [] as $field) {
                if (($field[0] ?? '') === 'fn' && ($field[3] ?? '') !== '') {
                    return (string) $field[3];
                }
            }
        }
        return null;
    }
}
