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

    private const LOCK_TIME = 60 * 60;

    /** @var Client $redis */
    private $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    /**
     * Блокировка пользователя.
     *
     * @param int $userId
     *
     * @return bool
     */
    public function lock(int $userId): bool
    {
        $this->redis->auth(self::PASSWORD);
        $result = $this->redis->setnx($this->key($userId), $this->getLockTime());

        if ($result === 1) {
            return true;
        }
        $expired = $this->redis->get($this->key($userId));

        if ($expired < time()) {
            $expired = $this->redis->getset($this->key($userId), $this->getLockTime());

            if ($expired > time()) {
                return false;
            }

            return true;
        }

        return false;
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

    /**
     * @return int
     */
    private function getLockTime(): int
    {
        return time() + self::LOCK_TIME + 1;
    }
}
