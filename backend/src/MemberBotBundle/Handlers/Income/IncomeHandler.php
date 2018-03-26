<?php

declare(strict_types = 1);

namespace MemberBotBundle\Handlers\Income;

use Doctrine\ORM\EntityManagerInterface;
use MemberBotBundle\Entity\Balance;
use MemberBotBundle\Event\Income as IncomeEvent;
use MemberBotBundle\Handlers\Exception\NotHandlerCommandException;
use MemberBotBundle\Handlers\Exception\UserLockedException;
use MemberBotBundle\Message\Income;
use MemberBotBundle\Repository\BalanceRepository;
use MemberBotBundle\Service\LockManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class IncomeHandler
{
    /** @var BalanceRepository */
    private $balanceRepository;

    /** @var LockManagerInterface */
    private $lockManager;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        ContainerInterface $container,
        LockManagerInterface $lockManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $this->balanceRepository = $entityManager->getRepository(Balance::class);
        $this->lockManager = $lockManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Обработка комманды Начисления денег.
     *
     * - Блокировка пользователя в редисе на время работы скрипты
     * - Перехватываем все exception, чтобы снять блокировку и прокидываем уровнем выше
     *
     * @param Income $command
     *
     * @throws NotHandlerCommandException
     * @throws UserLockedException
     *
     * @return bool
     */
    public function handle(Income $command): bool
    {
        $this->tryLock($command);

        try {
            $result = $this->balanceRepository->addSum($command->getUserId(), $command->getSum());
        } catch (\Exception $e) {
            $this->unlock($command);

            throw new NotHandlerCommandException($e->getMessage());
        }

        $this->unlock($command);
        $this->fireEvent($command);

        return $result;
    }

    /**
     * @param Income $command
     *
     * @throws UserLockedException
     */
    private function tryLock(Income $command): void
    {
        $canLocked = $this->lockManager->lock($command->getUserId());
        if ($canLocked === false) {
            throw new UserLockedException('Пользователь уже заблокирован');
        }
    }

    /**
     * @param Income $command
     */
    private function unlock(Income $command): void
    {
        $this->lockManager->unlock($command->getUserId());
    }

    /**
     * @param Income $command
     */
    private function fireEvent(Income $command): void
    {
        $this->eventDispatcher->dispatch(
            IncomeEvent::NAME,
            new IncomeEvent($command)
        );
    }
}
