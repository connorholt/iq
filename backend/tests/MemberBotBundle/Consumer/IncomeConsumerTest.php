<?php

declare(strict_types = 1);

namespace Tests\Consumer;

use Doctrine\ORM\EntityManager;
use MemberBotBundle\Command\Income;
use MemberBotBundle\Consumer\IncomeConsumer;
use MemberBotBundle\Entity\User;
use MemberBotBundle\Libraries\Message\IncomeMessage;
use MemberBotBundle\Libraries\StateMachine\User\Definition;
use MemberBotBundle\Repository\UserRepository;
use PhpAmqpLib\Message\AMQPMessage;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class IncomeConsumerTest extends KernelTestCase
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

    public function testExecute_UserExists(): void
    {
        $container = $this->container;

        // мок модели пользователя
        /** @var User $userMock */
        $userMock = $this->getUserMock();

        // мок репозиторий
        $userRepositoryMock = $this->getUserRepositoryMock();
        $userRepositoryMock->method('createIfNotExists')->willReturn($userMock);

        // сетим в репозиторий доктрину
        $entityMock = $this->getDoctrineMock($userRepositoryMock);
        $container->set('doctrine.orm.entity_manager', $entityMock);

        // мок для Definition
        $definitionMock = $this->getDefinitionMock();
        $definitionMock->expects($this->once())
            ->method('getCommandNameByState')
            ->with(Definition::STATE_IDLE)
            ->willReturn(Income::class);

        // получаем мок сообщения из реббита
        /** @var AMQPMessage $dirtyMessage */
        $dirtyMessage = $this->getMessageMock();

        $command = (new Income($userMock))->setBody((new IncomeMessage($userMock))->getBody());
        // собираем команд бас
        /** @var MessageBus $messageBus */
        $messageBus = $this->getMessageBusMock();
        $messageBus->expects($this->once())->method('handle')->with($command);

        $consumer = new IncomeConsumer($container, $messageBus, $definitionMock);

        $consumer->execute($dirtyMessage);
    }

    public function testExecute_NotUserExists(): void
    {
        $container = $this->container;

        /** @var User $userMock */
        $user = $this->getMockBuilder(User::class)
            ->setMethods(['getStatus'])
            ->getMock();
        $user->expects($this->never())
            ->method('getStatus')->willReturn(Definition::STATE_IDLE);

        // мок репозиторий
        $userRepositoryMock = $this->getUserRepositoryMock();
        $userRepositoryMock->expects($this->once())->method('createIfNotExists')->willReturn(null);

        // сетим в репозиторий доктрину
        $entityMock = $this->getDoctrineMock($userRepositoryMock);
        $container->set('doctrine.orm.entity_manager', $entityMock);

        // мок для Definition
        $definitionMock = $this->getDefinitionMock();
        $definitionMock->expects($this->never())
            ->method('getCommandNameByState')
            ->with(Definition::STATE_IDLE)
            ->willReturn(Income::class);

        // получаем мок сообщения из реббита
        /** @var AMQPMessage $dirtyMessage */
        $dirtyMessage = $this->getMessageMock();

        // собираем команд бас
        /** @var MessageBus $messageBus */
        $messageBus = $this->getMessageBusMock();
        $messageBus->expects($this->never())->method('handle');

        $consumer = new IncomeConsumer($container, $messageBus, $definitionMock);

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
            'ChatId' => 123, // random
            'Name' => 'Dmitriy',
            'Body' => 'Word',
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
            ->setMethods(['getCommandNameByState'])
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
