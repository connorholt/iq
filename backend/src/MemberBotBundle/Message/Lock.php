<?php

declare(strict_types = 1);

namespace MemberBotBundle\Message;

use MemberBotBundle\Service\MessageAdapterInterface;

class Lock implements MessageInterface
{
    private const EVENT_LOCK = 'lock';

    private const EVENT_WITHDRAW = 'withdraw';

    private const EVENT_UNLOCK = 'unlock';

    /** @var int */
    private $userId;

    /** @var int */
    private $sum;

    /** @var string */
    private $event;

    /** @var int */
    private $uuid;

    public function setData(MessageAdapterInterface $message): MessageInterface
    {
        $this->userId = $message->getUserId();
        $this->sum = $message->getSum();
        $this->event = $message->getDetails()['event'] ?? null;
        $this->uuid = $message->getDetails()['uuid'] ?? null;

        return $this;
    }

    public function getUserId(): int
    {
        return (int) $this->userId;
    }

    public function getSum(): int
    {
        return (int) $this->sum;
    }

    public function getUuid(): string
    {
        return (string) $this->uuid;
    }

    public function isLock(): bool
    {
        return $this->event === self::EVENT_LOCK;
    }

    public function isWithdraw(): bool
    {
        return $this->event === self::EVENT_WITHDRAW;
    }

    public function isUnlock(): bool
    {
        return $this->event === self::EVENT_UNLOCK;
    }
}
