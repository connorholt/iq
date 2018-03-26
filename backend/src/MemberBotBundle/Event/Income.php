<?php

declare(strict_types = 1);

namespace MemberBotBundle\Event;

use MemberBotBundle\Message\MessageInterface;

class Income implements EventInterface
{
    public const NAME = 'balance.income';

    private $command;

    public function __construct(MessageInterface $command)
    {
        $this->command = $command;
    }
}
