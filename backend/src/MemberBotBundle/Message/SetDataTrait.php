<?php

declare(strict_types = 1);

namespace MemberBotBundle\Message;

use MemberBotBundle\Service\MessageAdapterInterface;

trait SetDataTrait
{
    public function setData(MessageAdapterInterface $message): MessageInterface
    {
        $this->userId = $message->getUserId();
        $this->sum = $message->getSum();

        return $this;
    }
}
