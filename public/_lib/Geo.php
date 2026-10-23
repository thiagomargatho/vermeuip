<?php

declare(strict_types=1);

namespace Vermeuip;

use MaxMind\Db\Reader;

final class Geo
{
    private const UF = [
        'acre' => 'AC', 'alagoas' => 'AL', 'amapa' => 'AP', 'amazonas' => 'AM', 'bahia' => 'BA',
        'ceara' => 'CE', 'distrito federal' => 'DF', 'espirito santo' => 'ES', 'goias' => 'GO',
        'maranhao' => 'MA', 'mato grosso' => 'MT', 'mato grosso do sul' => 'MS', 'minas gerais' => 'MG',
        'para' => 'PA', 'paraiba' => 'PB', 'parana' => 'PR', 'pernambuco' => 'PE', 'piaui' => 'PI',
        'rio de janeiro' => 'RJ', 'rio grande do norte' => 'RN', 'rio grande do sul' => 'RS',
        'rondonia' => 'RO', 'roraima' => 'RR', 'santa catarina' => 'SC', 'sao paulo' => 'SP',
        'sergipe' => 'SE', 'tocantins' => 'TO',
    ];

    public function __construct(
        private readonly string $cityDb,
        private readonly string $asnDb
    ) {
    }

    /** @return array{cidade:?string,uf:?string,pais:?string,lat:?float,lon:?float,isp:?string,asn:?int} */
    public function lookup(string $ip): array
    {
        $out = ['cidade' => null, 'uf' => null, 'pais' => null, 'lat' => null, 'lon' => null, 'isp' => null, 'asn' => null];

        if (!Guard::isPublicIp($ip)) {
            return $out;
        }

        $city = $this->read($this->cityDb, $ip);
        if ($city !== null) {
            $out['cidade'] = $city['city']['names']['pt-BR'] ?? $city['city']['names']['en'] ?? null;
            $state = $city['subdivisions'][0]['names']['en'] ?? null;
            $out['uf'] = is_string($state) ? (self::ufFromName($state) ?? $state) : null;
            $out['pais'] = $city['country']['iso_code'] ?? null;
            $out['lat'] = isset($city['location']['latitude']) ? (float) $city['location']['latitude'] : null;
            $out['lon'] = isset($city['location']['longitude']) ? (float) $city['location']['longitude'] : null;
        }

        $asn = $this->read($this->asnDb, $ip);
        if ($asn !== null) {
            $out['asn'] = isset($asn['autonomous_system_number']) ? (int) $asn['autonomous_system_number'] : null;
            $out['isp'] = $asn['autonomous_system_organization'] ?? null;
        }

        return $out;
    }

    public static function ufFromName(string $name): ?string
    {
        $key = strtolower(trim($name));
        $key = strtr($key, ['á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'é' => 'e', 'ê' => 'e', 'í' => 'i',
            'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ú' => 'u', 'ç' => 'c']);
        return self::UF[$key] ?? null;
    }

    /** @return array<string,mixed>|null */
    private function read(string $path, string $ip): ?array
    {
        if (!is_file($path)) {
            return null;
        }
        try {
            $reader = new Reader($path);
            $record = $reader->get($ip);
            $reader->close();
        } catch (\Throwable) {
            return null;
        }
        return is_array($record) ? $record : null;
    }
}
