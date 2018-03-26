<?php

declare(strict_types = 1);

namespace MemberBotBundle\Event;

use MemberBotBundle\Message\MessageInterface;
use Symfony\Component\EventDispatcher\Event;

class Withdraw extends Event implements EventInterface
{
    public const NAME = 'balance.lock';

    /** @var MessageInterface|\MemberBotBundle\Message\Withdraw */
    private $command;

    public function __construct(MessageInterface $command)
    {
        $this->command = $command;
    }
}
