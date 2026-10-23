<?php

// Roda com `php -n` (sem php.ini: sem intl, curl, mbstring) para simular o host.
require __DIR__ . '/../../public/bootstrap.php';

echo Vermeuip\Guard::normalizeDomain('bücher.de') ?? 'null', PHP_EOL;
echo Vermeuip\Guard::normalizeDomain('https://Exemplo.COM./a') ?? 'null', PHP_EOL;
echo extension_loaded('intl') ? 'intl-carregada' : 'sem-intl', PHP_EOL;
