<?php

declare(strict_types = 1);

namespace MemberBotBundle\Handlers\Withdraw;

use Doctrine\ORM\EntityManagerInterface;
use MemberBotBundle\Entity\Balance;
use MemberBotBundle\Handlers\Exception\NotHandlerCommandException;
use MemberBotBundle\Handlers\Exception\UserLockedException;
use MemberBotBundle\Message\Withdraw;
use MemberBotBundle\Repository\BalanceRepository;
use MemberBotBundle\Service\LockManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WithdrawHandler
{
    /** @var BalanceRepository */
    private $balanceRepository;

    /** @var LockManagerInterface */
    private $lockManager;

    public function __construct(
        ContainerInterface $container,
        LockManagerInterface $lockManager
    ) {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $this->balanceRepository = $entityManager->getRepository(Balance::class);
        $this->lockManager = $lockManager;
    }

    /**
     * Обработка команды Списания средств.
     *
     * @param Withdraw $command
     *
     * @throws NotHandlerCommandException
     * @throws UserLockedException
     *
     * @return bool
     */
    public function handle(Withdraw $command): bool
    {
        $this->tryLock($command);

        try {
            $result = $this->balanceRepository->subSum($command->getUserId(), $command->getSum());
        } catch (\Exception $e) {
            $this->unlock($command);

            throw new NotHandlerCommandException($e->getMessage());
        }
        $this->unlock($command);

        return $result;
    }

    /**
     * @param Withdraw $command
     *
     * @throws UserLockedException
     */
    private function tryLock(Withdraw $command): void
    {
        $canLocked = $this->lockManager->lock($command->getUserId());
        if ($canLocked === false) {
            throw new UserLockedException('Пользователь уже заблокирован');
        }
    }

    /**
     * @param Withdraw $command
     */
    private function unlock(Withdraw $command): void
    {
        $this->lockManager->unlock($command->getUserId());
    }
}
