<?php

declare(strict_types=1);

namespace Vermeuip;

final class Config
{
    /** @return array<string,mixed> */
    public static function get(): array
    {
        static $config = null;
        return $config ??= require __DIR__ . '/settings.php';
    }
}
