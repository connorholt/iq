<?php

declare(strict_types = 1);

namespace MemberBotBundle\Event;

use MemberBotBundle\Message\MessageInterface;
use Symfony\Component\EventDispatcher\Event;

class Lock extends Event implements EventInterface
{
    public const NAME = 'balance.lock';

    /** @var MessageInterface|\MemberBotBundle\Message\Lock */
    private $command;

    public function __construct(MessageInterface $command)
    {
        $this->command = $command;
    }
}
