<?php

declare(strict_types = 1);

namespace MemberBotBundle\Handlers\Lock;

use Doctrine\ORM\EntityManagerInterface;
use MemberBotBundle\Entity\Balance;
use MemberBotBundle\Event\Lock as LockEvent;
use MemberBotBundle\Handlers\Exception\NotHandlerCommandException;
use MemberBotBundle\Handlers\Exception\UserLockedException;
use MemberBotBundle\Message\Lock;
use MemberBotBundle\Repository\BalanceRepository;
use MemberBotBundle\Service\LockManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class LockHandler
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
     * @param Lock $command
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function handle(Lock $command): bool
    {
        $this->tryLock($command);

        $lockHandler = new LockBalanceHandler($this->balanceRepository);
        $withdrawHandler = new LockAndWithdrawHandler($this->balanceRepository);
        $unlockHandler = new UnlockBalanceHandler($this->balanceRepository);

        $lockHandler->setNextHandler($withdrawHandler);
        $withdrawHandler->setNextHandler($unlockHandler);

        try {
            $result = $lockHandler->run($command);
        } catch (\Exception $e) {
            $this->unlock($command);

            throw new NotHandlerCommandException($e->getMessage());
        }
        $this->unlock($command);
        $this->fireEvent($command);

        return (bool) $result;
    }

    /**
     * @param Lock $command
     *
     * @throws \Exception
     */
    private function tryLock(Lock $command): void
    {
        $canLocked = $this->lockManager->lock($command->getUserId());
        if ($canLocked === false) {
            throw new UserLockedException('Пользователь уже заблокирован');
        }
    }

    /**
     * @param Lock $command
     */
    private function unlock(Lock $command): void
    {
        $this->lockManager->unlock($command->getUserId());
    }

    /**
     * @param Lock $command
     */
    private function fireEvent(Lock $command): void
    {
        $this->eventDispatcher->dispatch(
            LockEvent::NAME,
            new LockEvent($command)
        );
    }
}
