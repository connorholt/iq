<?php

declare(strict_types = 1);

namespace MemberBotBundle\Handlers\Lock;

use MemberBotBundle\Message\Lock;

trait ChainItemTrait
{
    /** @var null|ChainItemInterface */
    private $nextHandler = null;

    public function setNextHandler(ChainItemInterface $handler): ChainItemInterface
    {
        $this->nextHandler = $handler;

        return $this;
    }

    public function hasNextHandler(): bool
    {
        return $this->nextHandler !== null && $this->nextHandler instanceof ChainItemInterface;
    }

    public function run(Lock $command): ?bool
    {
        $result = $this->handle($command);

        if ($result === null && $this->hasNextHandler()) {
            return $this->nextHandler->run($command);
        }

        return $result;
    }
}
