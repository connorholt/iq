<?php

declare(strict_types = 1);

namespace MemberBotBundle\Message;

use MemberBotBundle\Service\MessageAdapterInterface;

interface MessageInterface
{
    public function setData(MessageAdapterInterface $data): self;
}
