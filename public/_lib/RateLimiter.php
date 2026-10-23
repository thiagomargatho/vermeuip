<?php

declare(strict_types=1);

namespace Vermeuip;

/** Contador efemero por chave (hash), janela de 60 s. Nao guarda o IP em claro. */
final class RateLimiter
{
    private const WINDOW = 60;

    /** @var callable():int */
    private $now;

    public function __construct(
        private readonly string $dir,
        private readonly int $maxPerMinute,
        ?callable $now = null
    ) {
        $this->now = $now ?? static fn (): int => time();
    }

    public function hit(string $key): bool
    {
        if (!is_dir($this->dir) && !@mkdir($this->dir, 0700, true) && !is_dir($this->dir)) {
            return true; // sem onde gravar: melhor esforco, nao bloqueia
        }

        $file = $this->dir . DIRECTORY_SEPARATOR . hash('sha256', $key);
        $fh = @fopen($file, 'c+');
        if ($fh === false) {
            return true;
        }

        flock($fh, LOCK_EX);
        $now = ($this->now)();
        [$start, $count] = array_map('intval', explode(' ', (string) stream_get_contents($fh)) + [0, 0]);

        if ($start === 0 || $now - $start >= self::WINDOW) {
            $start = $now;
            $count = 0;
        }
        $count++;

        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, $start . ' ' . $count);
        flock($fh, LOCK_UN);
        fclose($fh);

        return $count <= $this->maxPerMinute;
    }
}
