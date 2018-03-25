<?php

declare(strict_types = 1);

namespace MemberBotBundle\Message;

interface FactoryInterface
{
    public const TYPE_INCOME = 'income'; // Начисление денег

    public const TYPE_WITHDRAW = 'withdraw'; // Снятие денег

    public const TYPE_LOCK = 'lock'; // Блокировка с последующим снятие или разблокировкой

    public const TYPE_TRANSFER = 'transfer'; // Перевод денег между пользователями

    public function getCommand(?string $type): MessageInterface;
}
