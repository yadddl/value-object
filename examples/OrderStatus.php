<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Examples;

enum OrderStatus: string
{
    case Open = 'open';
    case Closed = 'closed';

    public function isOpen(): bool
    {
        return $this === self::Open;
    }
}
