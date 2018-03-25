<?php

declare(strict_types = 1);

namespace Tests\Consumer;

use Doctrine\ORM\EntityManager;
use MemberBotBundle\Command\Alarm;
use MemberBotBundle\Consumer\AlarmConsumer;
use MemberBotBundle\Entity\User;
use MemberBotBundle\Libraries\Message\AlarmMessage;
use MemberBotBundle\Libraries\StateMachine\User\Definition;
use MemberBotBundle\Planner\Holder;
use MemberBotBundle\Repository\UserRepository;
use PhpAmqpLib\Message\AMQPMessage;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AlarmConsumerTest extends KernelTestCase
{
    /** @var ContainerInterface */
    private $container;

    private $userMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();

        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->container = null;
    }

    public function testExecute_UserExists_Idle(): void
    {
        $container = $this->container;

        // мок модели пользователя
        /** @var User $userMock */
        $userMock = $this->getUserMock();

        // мок репозиторий
        $userRepositoryMock = $this->getUserRepositoryMock();
        $userRepositoryMock->method('findByQuantId')->willReturn($userMock);

        // сетим в репозиторий доктрину
        $entityMock = $this->getDoctrineMock($userRepositoryMock);
        $container->set('doctrine.orm.entity_manager', $entityMock);

        // мок для Definition
        $definitionMock = $this->getDefinitionMock();
        $definitionMock->expects($this->once())
            ->method('isStartPosition')
            ->with(Definition::STATE_IDLE)
            ->willReturn(true);

        // получаем мок сообщения из реббита
        /** @var AMQPMessage $dirtyMessage */
        $dirtyMessage = $this->getMessageMock();

        // собираем команд бас
        /** @var MessageBus $messageBus */
        $messageBus = $this->getMessageBusMock();
        $messageBus->expects($this->once())->method('handle')->with(new Alarm(
            new AlarmMessage($dirtyMessage),
            $userMock
        ));

        $consumer = new AlarmConsumer($container, $messageBus, $definitionMock);

        $consumer->execute($dirtyMessage);
    }

    public function testExecute_UserExists_NotIdle(): void
    {
        $container = $this->container;

        // мок модели пользователя
        /** @var User $userMock */
        $userMock = $this->getUserMock();

        // мок репозиторий
        $userRepositoryMock = $this->getUserRepositoryMock();
        $userRepositoryMock->method('findByQuantId')->willReturn($userMock);

        // сетим в репозиторий доктрину
        $entityMock = $this->getDoctrineMock($userRepositoryMock);
        $container->set('doctrine.orm.entity_manager', $entityMock);

        // мок для Definition
        $definitionMock = $this->getDefinitionMock();
        $definitionMock->expects($this->once())
            ->method('isStartPosition')
            ->willReturn(false);

        // получаем мок сообщения из реббита
        /** @var AMQPMessage $dirtyMessage */
        $dirtyMessage = $this->getMessageMock();

        // мок для Holder
        $holderMock = $this->getHolderMock();
        $holderMock->expects($this->once())
            ->method('hold')
            ->with(new AlarmMessage($dirtyMessage));

        // сетим все в контейнер
        $container->set(Holder::class, $holderMock);

        // собираем команд бас
        /** @var MessageBus $messageBus */
        $messageBus = $this->getMessageBusMock();
        $messageBus->expects($this->never())->method('handle')->with(new Alarm(
            new AlarmMessage($dirtyMessage),
            $userMock
        ));

        $consumer = new AlarmConsumer($container, $messageBus, $definitionMock);

        $consumer->execute($dirtyMessage);
    }

    public function testExecute_NotUserExists(): void
    {
        $container = $this->container;

        /** @var User $userMock */
        $user = $this->getMockBuilder(User::class)
            ->setMethods(['getStatus'])
            ->getMock();
        $user->expects($this->never())->method('getStatus');

        // мок репозиторий
        $userRepositoryMock = $this->getUserRepositoryMock();
        $userRepositoryMock->expects($this->once())->method('findByQuantId')->willReturn(null);

        // сетим в репозиторий доктрину
        $entityMock = $this->getDoctrineMock($userRepositoryMock);
        $container->set('doctrine.orm.entity_manager', $entityMock);

        // мок для Definition
        $definitionMock = $this->getDefinitionMock();
        $definitionMock->expects($this->never())->method('isStartPosition');

        // получаем мок сообщения из реббита
        /** @var AMQPMessage $dirtyMessage */
        $dirtyMessage = $this->getMessageMock();

        // собираем команд бас
        /** @var MessageBus $messageBus */
        $messageBus = $this->getMessageBusMock();
        $messageBus->expects($this->never())->method('handle');

        $consumer = new AlarmConsumer($container, $messageBus, $definitionMock);

        $consumer->execute($dirtyMessage);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getUserMock(): \PHPUnit\Framework\MockObject\MockObject
    {
        $user = $this->getMockBuilder(User::class)
            ->setMethods(['getStatus'])
            ->getMock();
        $user->expects($this->once())
            ->method('getStatus')->willReturn(Definition::STATE_IDLE);
        $this->userMock = $user;

        return $user;
    }

    /**
     * @param $userRepositoryMock
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getDoctrineMock($userRepositoryMock): \PHPUnit\Framework\MockObject\MockObject
    {
        $entityMock = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityMock->method('getRepository')->with(User::class)->willReturn($userRepositoryMock);

        return $entityMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getMessageMock(): \PHPUnit\Framework\MockObject\MockObject
    {
        $dirtyMessage = $this->getMockBuilder(AMQPMessage::class)
            ->setMethods(['getBody'])
            ->getMock();
        $dirtyMessage->method('getBody')->willReturn(json_encode([
            'QuantId' => 123,
        ]));

        return $dirtyMessage;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getUserRepositoryMock(): \PHPUnit\Framework\MockObject\MockObject
    {
        $userRepositoryMock = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $userRepositoryMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getDefinitionMock(): \PHPUnit\Framework\MockObject\MockObject
    {
        $definitionMock = $this->getMockBuilder(Definition::class)
            ->setMethods(['isStartPosition'])
            ->getMock();

        return $definitionMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getHolderMock(): \PHPUnit\Framework\MockObject\MockObject
    {
        $definitionMock = $this->getMockBuilder(Holder::class)
            ->disableOriginalConstructor()
            ->setMethods(['hold'])
            ->getMock();

        return $definitionMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getMessageBusMock(): \PHPUnit\Framework\MockObject\MockObject
    {
        $messageBus = $this->getMockBuilder(MessageBus::class)
            ->setMethods(['handle'])
            ->getMock();

        return $messageBus;
    }
}
