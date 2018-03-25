<?php

declare(strict_types = 1);

namespace MemberBotBundle\Service;

interface MessageAdapterInterface
{
    public function getType(): string;

    public function getUserId(): int;

    public function getSum(): int;

    public function getDetails(): array;
}
