<?php

declare(strict_types=1);

namespace Vermeuip;

/** O servidor nao permite abrir conexoes de saida (comum em hospedagem compartilhada). */
final class OutboundBlocked extends \RuntimeException
{
}
