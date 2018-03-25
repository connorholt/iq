<?php

declare(strict_types = 1);

namespace MemberBotBundle\Message;

class Factory implements FactoryInterface
{
    public function getCommand(?string $type): MessageInterface
    {
        switch ($type) {
            case self::TYPE_INCOME: return new Income();
            case self::TYPE_WITHDRAW: return new Withdraw();
            case self::TYPE_LOCK: return new Lock();
            case self::TYPE_TRANSFER: return new Transfer();
        }

        throw new NotFoundMessageClass('Не найден класс для обработки сообщения');
    }
}
