<?php

declare(strict_types = 1);

namespace MemberBotBundle\Service;

use Predis\Client;

class LockManager implements LockManagerInterface
{
    /**
     * Пароль для редиса.
     *
     * @todo нужно вынести в конфиг
     */
    private const PASSWORD = 'master_password';

    /** @var Client $redis */
    private $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    /**
     * Блокировка пользователя.
     *
     * @todo можно добавить проверку на время если к time() добавить n секунд, а потом проверять
     * @todo для скорости выполнения задания эту проверку не стал реализовывать
     *
     * @param int $userId
     *
     * @return bool
     */
    public function lock(int $userId): bool
    {
        $this->redis->auth(self::PASSWORD);
        $result = $this->redis->setnx($this->key($userId), time());

        return (bool) ($result === 1);
    }

    /**
     * Разбллокировка пользователя.
     *
     * @param int $userId
     *
     * @return bool
     */
    public function unlock(int $userId): bool
    {
        $this->redis->auth(self::PASSWORD);
        $result = $this->redis->del($this->key($userId));

        return (bool) $result;
    }

    /**
     * Префикс для ключа в редисе.
     *
     * @param int $id
     *
     * @return string
     */
    private function key(int $id): string
    {
        return self::PREFIX . (string) $id;
    }
}
