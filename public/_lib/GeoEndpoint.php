<?php

declare(strict_types=1);

namespace Vermeuip;

final class GeoEndpoint
{
    /**
     * @param array<string,mixed> $server
     * @return array{ip:string,ptr:?string,cidade:?string,uf:?string,pais:?string,lat:?float,lon:?float,isp:?string,asn:?int}
     */
    public static function payload(array $server): array
    {
        $config = Config::get();
        $ip = ClientIp::resolve($server, $config['TRUSTED_PROXIES']);
        $geo = (new Geo($config['GEO_CITY_DB'], $config['GEO_ASN_DB']))->lookup($ip);

        return ['ip' => $ip, 'ptr' => Ptr::lookup($ip)] + $geo;
    }
}
