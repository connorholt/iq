<?php

declare(strict_types = 1);

namespace MemberBotBundle\Event;

use MemberBotBundle\Message\MessageInterface;

class Transfer implements EventInterface
{
    public const NAME = 'balance.transfer';

    private $command;

    public function __construct(MessageInterface $command)
    {
        $this->command = $command;
    }
}
