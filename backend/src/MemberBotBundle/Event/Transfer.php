<?php

declare(strict_types = 1);

namespace MemberBotBundle\Event;

use MemberBotBundle\Message\MessageInterface;
use Symfony\Component\EventDispatcher\Event;

class Transfer extends Event implements EventInterface
{
    public const NAME = 'balance.transfer';

    /** @var MessageInterface|\MemberBotBundle\Message\Transfer */
    private $command;

    public function __construct(MessageInterface $command)
    {
        $this->command = $command;
    }
}
