<?php

declare(strict_types=1);

namespace Vermeuip;

final class DnsLookup
{
    private const TYPES = [
        'A' => DNS_A, 'AAAA' => DNS_AAAA, 'MX' => DNS_MX, 'TXT' => DNS_TXT,
        'NS' => DNS_NS, 'CNAME' => DNS_CNAME, 'SOA' => DNS_SOA,
    ];

    /** @var callable(string,int):(array<int,array<string,mixed>>|false) */
    private $resolver;

    /** @param null|callable(string,int):(array<int,array<string,mixed>>|false) $resolver */
    public function __construct(?callable $resolver = null)
    {
        $this->resolver = $resolver ?? static fn (string $d, int $t) => @dns_get_record($d, $t);
    }

    public static function supports(string $type): bool
    {
        return isset(self::TYPES[strtoupper($type)]);
    }

    /** @return list<array{tipo:string,valor:string,ttl:int}> */
    public function query(string $domain, string $type): array
    {
        $type = strtoupper($type);
        if (!isset(self::TYPES[$type])) {
            throw new \InvalidArgumentException('Tipo de registro inválido.');
        }

        $records = ($this->resolver)($domain, self::TYPES[$type]);
        if (!is_array($records)) {
            return [];
        }

        $out = [];
        foreach ($records as $r) {
            $out[] = ['tipo' => $type, 'valor' => $this->value($type, $r), 'ttl' => (int) ($r['ttl'] ?? 0)];
        }
        return $out;
    }

    /** @param array<string,mixed> $r */
    private function value(string $type, array $r): string
    {
        return match ($type) {
            'A' => (string) ($r['ip'] ?? ''),
            'AAAA' => (string) ($r['ipv6'] ?? ''),
            'MX' => ($r['pri'] ?? '') . ' ' . ($r['target'] ?? ''),
            'TXT' => (string) ($r['txt'] ?? (isset($r['entries']) ? implode('', $r['entries']) : '')),
            'NS', 'CNAME' => (string) ($r['target'] ?? ''),
            'SOA' => implode(' ', [$r['mname'] ?? '', $r['rname'] ?? '', $r['serial'] ?? '', $r['refresh'] ?? '',
                $r['retry'] ?? '', $r['expire'] ?? '', $r['minimum-ttl'] ?? '']),
            default => '',
        };
    }
}
