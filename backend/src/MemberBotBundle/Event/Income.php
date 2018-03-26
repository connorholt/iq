<?php

declare(strict_types = 1);

namespace MemberBotBundle\Event;

use MemberBotBundle\Message\MessageInterface;
use Symfony\Component\EventDispatcher\Event;

class Income extends Event implements EventInterface
{
    public const NAME = 'balance.income';

    /** @var MessageInterface|\MemberBotBundle\Message\Income */
    private $command;

    public function __construct(MessageInterface $command)
    {
        $this->command = $command;
    }
}
