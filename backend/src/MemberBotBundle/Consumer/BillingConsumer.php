<?php

declare(strict_types = 1);

namespace MemberBotBundle\Consumer;

use Doctrine\ORM\EntityManagerInterface;
use MemberBotBundle\Message\FactoryInterface;
use MemberBotBundle\Message\MessageInterface;
use MemberBotBundle\Message\NotFoundMessageClass;
use MemberBotBundle\Service\MessageAdapter;
use MemberBotBundle\Service\MessageAdapterInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BillingConsumer.
 */
class BillingConsumer implements ConsumerInterface
{
    /** @var MessageBus|object */
    private $commandBus;

    /** @var ContainerInterface $container */
    private $container;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var FactoryInterface $commandFactory */
    private $commandFactory;

    public function __construct(
        ContainerInterface $container,
        MessageBus $commandBus,
        FactoryInterface $commandFactory
    ) {
        $this->container = $container;
        $this->commandBus = $commandBus;
        $this->commandFactory = $commandFactory;

        $this->entityManager = $container->get('doctrine.orm.entity_manager');

        gc_enable();
    }

    /**
     * Обработка сообщений из очереди.
     *
     * @param AMQPMessage $dirtyMessage
     *
     * @return int
     */
    public function execute(AMQPMessage $dirtyMessage): int
    {
        /** @var MessageAdapterInterface $message */
        $message = new MessageAdapter($dirtyMessage);

        try {
            /** @var MessageInterface $command */
            $command = $this->commandFactory->getCommand($message->getType());
        } catch (NotFoundMessageClass $e) {
            return $this->completeExecute(self::MSG_REJECT);
        }

        try {
            $this->commandBus->handle($command->setData($message));
        } catch (\Exception $e) {
            /** @var LoggerInterface $logger */
            $logger = $this->container->get('logger');
            $logger->error($e->getMessage(), ['class' => get_class($e)]);

            return $this->completeExecute(self::MSG_REJECT);
        }

        return $this->completeExecute(self::MSG_ACK);
    }

    /**
     * Корректное завершение работы консьюмера
     * Закрытие соединения с базой, сбор мусора.
     *
     * @param int $status
     *
     * @return int
     */
    public function completeExecute(int $status): int
    {
        $this->entityManager->clear();
        $this->entityManager->getConnection()->close();

        gc_collect_cycles();

        return $status;
    }
}
