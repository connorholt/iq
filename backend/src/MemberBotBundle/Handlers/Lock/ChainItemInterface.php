<?php

declare(strict_types = 1);

namespace MemberBotBundle\Handlers\Lock;

use MemberBotBundle\Message\Lock;

interface ChainItemInterface
{
    public function setNextHandler(self $handler): self;

    public function hasNextHandler(): bool;

    public function run(Lock $command): ?bool;

    public function handle(Lock $command): ?bool;
}
