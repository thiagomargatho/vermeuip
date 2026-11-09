<?php

declare(strict_types=1);

namespace Vermeuip;

/** Cabecalhos HTTP que o navegador do visitante enviou, prontos para exibir. */
final class RequestHeaders
{
    /** Nunca exibidos: credenciais e cookies. */
    private const OCULTOS = ['COOKIE', 'AUTHORIZATION', 'PROXY_AUTHORIZATION'];
    private const MAX = 500;

    /**
     * @param array<string,mixed> $server
     * @return list<array{nome:string,valor:string}> User-Agent primeiro, o resto em ordem alfabetica
     */
    public static function visible(array $server): array
    {
        $out = [];
        foreach ($server as $chave => $valor) {
            if (!is_string($chave) || !str_starts_with($chave, 'HTTP_') || !is_string($valor)) {
                continue;
            }
            $bruto = substr($chave, 5);
            if (in_array($bruto, self::OCULTOS, true)) {
                continue;
            }
            $nome = implode('-', array_map(
                static fn (string $p): string => ucfirst(strtolower($p)),
                explode('_', $bruto)
            ));
            $out[] = ['nome' => $nome, 'valor' => substr($valor, 0, self::MAX)];
        }

        usort($out, static function (array $a, array $b): int {
            $ua = ($b['nome'] === 'User-Agent') <=> ($a['nome'] === 'User-Agent');
            return $ua !== 0 ? $ua : strcmp($a['nome'], $b['nome']);
        });

        return $out;
    }
}
