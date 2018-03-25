<?php

declare(strict_types = 1);

namespace MemberBotBundle\Message;

class Withdraw implements MessageInterface
{
    use SetDataTrait;

    /** @var int */
    private $userId;

    /** @var int */
    private $sum;

    public function getUserId(): int
    {
        return (int) $this->userId;
    }

    public function getSum(): int
    {
        return (int) $this->sum;
    }
}
