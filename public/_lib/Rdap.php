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

    /** GET com curl: timeout curto, so https, ate 3 redirecionamentos. null = 404. */
    public static function curlGet(string $url): ?array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_HTTPHEADER => ['Accept: application/rdap+json, application/json'],
        ]);
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            throw new \RuntimeException($err);
        }
        if ($code === 404) {
            return null;
        }
        $data = json_decode((string) $body, true);
        if ($code >= 400 || !is_array($data)) {
            throw new \RuntimeException('RDAP HTTP ' . $code);
        }
        return $data;
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
