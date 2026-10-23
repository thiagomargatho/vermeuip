<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Vermeuip\Guard;

final class GuardTest extends TestCase
{
    public function test_public_ip_is_accepted(): void
    {
        $this->assertTrue(Guard::isPublicIp('177.76.43.250'));
        $this->assertTrue(Guard::isPublicIp('2804:14d::1'));
    }

    /** @dataProvider nonPublicIps */
    public function test_non_public_ip_is_rejected(string $ip): void
    {
        $this->assertFalse(Guard::isPublicIp($ip));
    }

    public static function nonPublicIps(): array
    {
        return [['127.0.0.1'], ['10.0.0.5'], ['192.168.1.1'], ['169.254.1.1'], ['::1'], ['fc00::1'], ['nao-e-ip']];
    }

    public function test_domain_is_normalized(): void
    {
        $this->assertSame('exemplo.com', Guard::normalizeDomain('https://Exemplo.COM./a?b'));
        $this->assertSame('xn--bcher-kva.de', Guard::normalizeDomain('bücher.de'));
    }

    /** @dataProvider badDomains */
    public function test_bad_domain_is_rejected(string $input): void
    {
        $this->assertNull(Guard::normalizeDomain($input));
    }

    public static function badDomains(): array
    {
        return [['8.8.8.8'], ['localhost'], [''], ['sem espaco.com'], ['semponto'], ['[::1]']];
    }

    public function test_port_parsing(): void
    {
        $this->assertSame(443, Guard::parsePort('443'));
        $this->assertSame(1, Guard::parsePort(1));
        $this->assertSame(65535, Guard::parsePort('65535'));
        foreach (['0', '65536', '', 'abc', '-1', '80.5', null] as $bad) {
            $this->assertNull(Guard::parsePort($bad), var_export($bad, true));
        }
    }
}
