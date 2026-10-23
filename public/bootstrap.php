<?php

declare(strict_types=1);

// Carrega as dependencias (vendor/) e as classes Vermeuip\ de _lib/.
// O mapeamento do Composer para Vermeuip\ depende de onde a pasta "public" fica
// em relacao ao vendor; no cPanel ela e a propria raiz do site, entao registramos
// o autoload de _lib/ aqui, relativo a este arquivo.
require __DIR__ . '/vendor/autoload.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'Vermeuip\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $file = __DIR__ . '/_lib/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});
