<?php

declare(strict_types = 1);

namespace MemberBotBundle\Message;

use MemberBotBundle\Service\MessageAdapterInterface;

class Transfer implements MessageInterface
{
    /** @var int */
    private $userId;

    /** @var int */
    private $userFromId;

    /** @var int */
    private $sum;

    public function setData(MessageAdapterInterface $message): MessageInterface
    {
        $this->userId = $message->getUserId();
        $this->sum = $message->getSum();
        $this->userFromId = $message->getDetails()['userFromId'] ?? null;

        return $this;
    }

    public function getUserId(): int
    {
        return (int) $this->userId;
    }

    public function getUserFromId(): int
    {
        return (int) $this->userFromId;
    }

    public function getSum(): int
    {
        return (int) $this->sum;
    }
}
