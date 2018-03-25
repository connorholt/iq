<?php

declare(strict_types = 1);

namespace MemberBotBundle\Service;

class Sum
{
    const FACTOR = 1000;

    public static function set(int $value): int
    {
        return $value * self::FACTOR;
    }
}
