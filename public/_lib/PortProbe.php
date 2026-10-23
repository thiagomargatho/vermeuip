<?php

declare(strict_types=1);

namespace Vermeuip;

final class PortProbe
{
    private const BLOCKED_ERRNO = [1, 13, 10013]; // EPERM, EACCES (Linux), WSAEACCES (Windows)

    /**
     * @param null|callable(string,float):bool $connector true = conexao aceita; lanca OutboundBlocked se o host bloqueia saida
     * @return 'aberta'|'fechada_ou_filtrada'
     */
    public static function check(string $ip, int $port, float $timeout = 3.0, ?callable $connector = null): string
    {
        $address = (str_contains($ip, ':') ? "tcp://[$ip]:$port" : "tcp://$ip:$port");
        $connector ??= [self::class, 'connect'];

        return $connector($address, $timeout) ? 'aberta' : 'fechada_ou_filtrada';
    }

    public static function connect(string $address, float $timeout): bool
    {
        if (!function_exists('stream_socket_client')) {
            throw new OutboundBlocked();
        }

        $errno = 0;
        $errstr = '';
        $socket = @stream_socket_client($address, $errno, $errstr, $timeout);
        if ($socket === false) {
            if (in_array($errno, self::BLOCKED_ERRNO, true)) {
                throw new OutboundBlocked();
            }
            return false;
        }

        fclose($socket);
        return true;
    }
}
