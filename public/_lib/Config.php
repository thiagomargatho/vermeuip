<?php

declare(strict_types=1);

namespace Vermeuip;

final class Config
{
    public const ENGINES = ['cloudflare', 'librespeed'];

    /** @return array<string,mixed> */
    public static function get(): array
    {
        static $config = null;
        if ($config === null) {
            $config = require __DIR__ . '/settings.php';
            $config['SPEED_ENGINE'] = self::normalizeEngine((string) ($config['SPEED_ENGINE'] ?? ''));
        }
        return $config;
    }

    public static function normalizeEngine(string $engine): string
    {
        return in_array($engine, self::ENGINES, true) ? $engine : 'cloudflare';
    }
}
