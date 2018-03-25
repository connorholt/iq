<?php

declare(strict_types = 1);

namespace MemberBotBundle\Service;

interface LockManagerInterface
{
    public const PREFIX = 'lock:';

    public function lock(int $userId): bool;

    public function unlock(int $userId): bool;
}
