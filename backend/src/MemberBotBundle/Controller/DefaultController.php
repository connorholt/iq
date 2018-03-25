<?php

declare(strict_types = 1);

namespace MemberBotBundle\Controller;

use MemberBotBundle\Entity\Balance;
use MemberBotBundle\Service\Sum;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="seed")
     *
     * Для тестирования
     */
    public function indexAction(): Response
    {
        $manager = $this->getDoctrine()->getManager();

        for ($i = 1; $i < 200; ++$i) {
            $product = new Balance();
            $product->setUserId($i);
            $product->setSum(Sum::set(random_int(0, 1000)));

            $manager->persist($product);
        }

        $manager->flush();

        return new Response('Seed data');
    }

    /**
     * @Route("/test", name="test")
     *
     * Для тестирования
     */
    public function testAction(): Response
    {
        /** @var ProducerInterface $publisher */
        $publisher = $this->container->get('old_sound_rabbit_mq.billing_producer');

        $encodedData = json_encode([
            'type' => 'income',
            'userId' => 2,
            'sum' => 1,
            'details' => [],
        ]);

        if ($encodedData) {
            $publisher->publish($encodedData);
        }

        return new Response('Add to queue');
    }

    /**
     * @Route("/withdraw", name="withdraw")
     *
     * Для тестирования
     */
    public function withdrawAction(): Response
    {
        /** @var ProducerInterface $publisher */
        $publisher = $this->container->get('old_sound_rabbit_mq.billing_producer');

        $encodedData = json_encode([
            'type' => 'withdraw',
            'userId' => 2,
            'sum' => 2,
            'details' => [],
        ]);

        if ($encodedData) {
            $publisher->publish($encodedData);
        }

        return new Response('Add to queue');
    }

    /**
     * @Route("/lock", name="lock")
     *
     * Для тестирования
     */
    public function lockAction(): Response
    {
        /** @var ProducerInterface $publisher */
        $publisher = $this->container->get('old_sound_rabbit_mq.billing_producer');

        $encodedData = json_encode([
            'type' => 'lock', // lock withdraw unlock
            'userId' => 2,
            'sum' => 2,
            'details' => [
                'event' => 'lock',
                'uuid' => 'id2',
            ],
        ]);

        if ($encodedData) {
            $publisher->publish($encodedData);
        }

        return new Response('Add to queue');
    }

    /**
     * @Route("/lockWithdraw", name="lockWithdraw")
     *
     * Для тестирования
     */
    public function lockWithdrawAction(): Response
    {
        /** @var ProducerInterface $publisher */
        $publisher = $this->container->get('old_sound_rabbit_mq.billing_producer');

        $encodedData = json_encode([
            'type' => 'lock', // lock withdraw unlock
            'userId' => 2,
            'sum' => 2,
            'details' => [
                'event' => 'withdraw',
                'uuid' => 'id2',
            ],
        ]);

        if ($encodedData) {
            $publisher->publish($encodedData);
        }

        return new Response('Add to queue');
    }

    /**
     * @Route("/lockWithdraw", name="lockWithdraw")
     *
     * Для тестирования
     */
    public function unlockAction(): Response
    {
        /** @var ProducerInterface $publisher */
        $publisher = $this->container->get('old_sound_rabbit_mq.billing_producer');

        $encodedData = json_encode([
            'type' => 'lock', // lock withdraw unlock
            'userId' => 2,
            'sum' => 2,
            'details' => [
                'event' => 'unlock',
                'uuid' => 'id2',
            ],
        ]);

        if ($encodedData) {
            $publisher->publish($encodedData);
        }

        return new Response('Add to queue');
    }

    /**
     * @Route("/transfer", name="transfer")
     *
     * Для тестирования
     */
    public function transferAction(): Response
    {
        /** @var ProducerInterface $publisher */
        $publisher = $this->container->get('old_sound_rabbit_mq.billing_producer');

        $encodedData = json_encode([
            'type' => 'transfer',
            'userId' => 2,
            'sum' => 5,
            'details' => [
                'userFromId' => 3,
            ],
        ]);

        if ($encodedData) {
            $publisher->publish($encodedData);
        }

        return new Response('Add to queue');
    }
}
