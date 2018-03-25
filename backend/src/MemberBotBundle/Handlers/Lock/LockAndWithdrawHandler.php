<?php

declare(strict_types = 1);

namespace MemberBotBundle\Handlers\Lock;

use MemberBotBundle\Message\Lock;
use MemberBotBundle\Repository\BalanceRepository;

class LockAndWithdrawHandler implements ChainItemInterface
{
    use ChainItemTrait;

    /** @var BalanceRepository */
    private $balanceRepository;

    public function __construct(BalanceRepository $balanceRepository)
    {
        $this->balanceRepository = $balanceRepository;
    }

    public function handle(Lock $command): ?bool
    {
        if ($command->isWithdraw()) {
            return $this->balanceRepository->unlockAndSubSum(
                $command->getUserId(),
                $command->getUuid(),
                $command->getSum()
            );
        }

        return null;
    }
}
