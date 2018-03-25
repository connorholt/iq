<?php

declare(strict_types = 1);

namespace MemberBotBundle\Service;

use PhpAmqpLib\Message\AMQPMessage;

class MessageAdapter implements MessageAdapterInterface
{
    /** @var array */
    private $decodedMessage;

    public function __construct(AMQPMessage $dirtyMessage)
    {
        $this->decodedMessage = json_decode($dirtyMessage->getBody(), true);
    }

    public function getType(): string
    {
        return (string) ($this->decodedMessage['type'] ?? null);
    }

    public function getUserId(): int
    {
        $userId = $this->decodedMessage['userId'] ?? null;

        return (int) $userId;
    }

    public function getSum(): int
    {
        $sum = $this->decodedMessage['sum'] ?? 0;

        return Sum::set($sum);
    }

    public function getDetails(): array
    {
        return $this->decodedMessage['details'] ?? [];
    }
}
