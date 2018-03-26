<?php

declare(strict_types = 1);

namespace MemberBotBundle\Event;

use MemberBotBundle\Message\MessageInterface;

class Withdraw implements EventInterface
{
    public const NAME = 'balance.lock';

    private $command;

    public function __construct(MessageInterface $command)
    {
        $this->command = $command;
    }
}
