<?php

declare(strict_types = 1);

namespace MemberBotBundle\Handlers\Transfer;

use Doctrine\ORM\EntityManagerInterface;
use MemberBotBundle\Entity\Balance;
use MemberBotBundle\Handlers\Exception\NotHandlerCommandException;
use MemberBotBundle\Handlers\Exception\UserLockedException;
use MemberBotBundle\Message\Transfer;
use MemberBotBundle\Repository\BalanceRepository;
use MemberBotBundle\Service\LockManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TransferHandler
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
     * Обработчик сообщения о перевод стредств от пользователя пользователю.
     *
     * @param Transfer $command
     *
     * @throws NotHandlerCommandException
     * @throws UserLockedException
     *
     * @return bool
     */
    public function handle(Transfer $command): bool
    {
        $this->tryLock($command);

        try {
            $result = $this->balanceRepository->transferSum(
                $command->getUserId(),
                $command->getUserFromId(),
                $command->getSum()
            );
        } catch (\Exception $e) {
            $this->unlock($command);

            throw new NotHandlerCommandException($e->getMessage());
        }

        $this->unlock($command);

        return $result;
    }

    /**
     * Попытка залочить пользователей, чтобы произвести транзакции.
     *
     * @param Transfer $command
     *
     * @throws UserLockedException
     */
    private function tryLock(Transfer $command): void
    {
        $canLocked = $this->lockManager->lock($command->getUserId());
        if ($canLocked === false) {
            throw new UserLockedException('Пользователь уже заблокирован');
        }

        $canLockedFrom = $this->lockManager->lock($command->getUserFromId());
        if ($canLockedFrom === false) {
            $this->lockManager->unlock($command->getUserId());

            throw new UserLockedException('Пользователь уже заблокирован');
        }
    }

    /**
     * Разблокировка пользователей.
     *
     * @param Transfer $command
     */
    private function unlock(Transfer $command): void
    {
        $this->lockManager->unlock($command->getUserId());
        $this->lockManager->unlock($command->getUserFromId());
    }
}
