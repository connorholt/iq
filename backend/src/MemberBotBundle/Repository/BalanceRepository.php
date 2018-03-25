<?php

declare(strict_types = 1);

namespace MemberBotBundle\Repository;

use Doctrine\ORM\Query;
use MemberBotBundle\Entity\Balance;
use MemberBotBundle\Entity\BlockedMoney;
use MemberBotBundle\Entity\TransactionLog;

/**
 * BalanceRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BalanceRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Начисление денег.
     *
     * @param int $userId
     * @param int $sum
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     *
     * @return bool
     */
    public function addSum(int $userId, int $sum): bool
    {
        $this->getEntityManager()->getConnection()->beginTransaction();

        try {
            $balanceQuery = $this->getAddSumQuery($userId, $sum);
            $balanceQuery->execute();

            $this->saveTransaction($userId, $sum, TransactionLog::TYPE_INCOME);

            $this->getEntityManager()->getConnection()->commit();

            return true;
        } catch (\Exception $e) {
            $this->getEntityManager()->getConnection()->rollBack();

            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Списание денег.
     *
     * @param int $userId
     * @param int $sum
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     *
     * @return bool
     */
    public function subSum(int $userId, int $sum): bool
    {
        $this->getEntityManager()->getConnection()->beginTransaction();

        try {
            $balanceQuery = $this->getSubSumQuery($userId, $sum);
            $balanceQuery->execute();

            $this->saveTransaction($userId, $sum, TransactionLog::TYPE_WITHDRAW);

            $this->getEntityManager()->getConnection()->commit();

            return true;
        } catch (\Exception $e) {
            $this->getEntityManager()->getConnection()->rollBack();

            throw $e;
        }
    }

    public function transferSum(int $userId, int $userFromId, int $sum): bool
    {
        $this->getEntityManager()->getConnection()->beginTransaction();

        try {
            $balanceAddQuery = $this->getAddSumQuery($userId, $sum);
            $balanceAddQuery->execute();

            $balanceSubQuery = $this->getSubSumQuery($userFromId, $sum);
            $balanceSubQuery->execute();

            $this->saveTransaction(
                $userId,
                $sum,
                TransactionLog::TYPE_TRANSFER,
                [
                    'user_from_id' => $userFromId,
                ]
            );

            $this->getEntityManager()->getConnection()->commit();

            return true;
        } catch (\Exception $e) {
            $this->getEntityManager()->getConnection()->rollBack();

            throw $e;
        }
    }

    public function lockSum(int $userId, string $uuid, int $sum): bool
    {
        $this->getEntityManager()->getConnection()->beginTransaction();

        try {
            $blockedMoney = new BlockedMoney();
            $blockedMoney->setSum($sum);
            $blockedMoney->setUserId($userId);
            $blockedMoney->setUuid($uuid);

            $this->getEntityManager()->persist($blockedMoney);
            $this->getEntityManager()->flush();

            $balanceSubQuery = $this->getSubSumQuery($userId, $sum);
            $balanceSubQuery->execute();

            $this->saveTransaction(
                $userId,
                $sum,
                TransactionLog::TYPE_LOCK,
                [
                    'event' => 'lock',
                    'uuid' => $uuid,
                ]
            );

            $this->getEntityManager()->getConnection()->commit();

            return true;
        } catch (\Exception $e) {
            $this->getEntityManager()->getConnection()->rollBack();

            throw $e;
        }
    }

    public function unlockAndSubSum(int $userId, string $uuid, int $sum): bool
    {
        $this->getEntityManager()->getConnection()->beginTransaction();

        try {
            $deleteBlockedMoneyQuery = $this->getDeleteBlockedMoneyQuery($userId, $uuid);
            $deleteBlockedMoneyQuery->execute();

            $this->saveTransaction(
                $userId,
                $sum,
                TransactionLog::TYPE_LOCK,
                [
                    'event' => 'unlockAndSubSum',
                    'uuid' => $uuid,
                ]
            );

            $this->getEntityManager()->getConnection()->commit();

            return true;
        } catch (\Exception $e) {
            $this->getEntityManager()->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * Удаляем строку из таблицы blocked_money
     * Добавляем сумму обратно пользователю.
     *
     * @param int    $userId
     * @param string $uuid
     * @param int    $sum
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     *
     * @return bool
     */
    public function unlockSum(int $userId, string $uuid, int $sum): bool
    {
        $this->getEntityManager()->getConnection()->beginTransaction();

        try {
            $deleteBlockedMoneyQuery = $this->getDeleteBlockedMoneyQuery($userId, $uuid);
            $deleteBlockedMoneyQuery->execute();

            $balanceAddQuery = $this->getAddSumQuery($userId, $sum);
            $balanceAddQuery->execute();

            $this->saveTransaction(
                $userId,
                $sum,
                TransactionLog::TYPE_LOCK,
                [
                    'event' => 'unlock',
                    'uuid' => $uuid,
                ]
            );

            $this->getEntityManager()->getConnection()->commit();

            return true;
        } catch (\Exception $e) {
            $this->getEntityManager()->getConnection()->rollBack();

            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param int   $userId
     * @param int   $sum
     * @param int   $type
     * @param array $details
     */
    private function saveTransaction(
        int $userId,
        int $sum,
        int $type,
        array $details = []
    ): void {
        $transaction = new TransactionLog();
        $transaction->setSum($sum);
        $transaction->setUserId($userId);
        $transaction->setType($type);
        $transaction->setDetails(json_encode($details));

        $this->getEntityManager()->persist($transaction);
        $this->getEntityManager()->flush();
    }

    /**
     * @param int $userId
     * @param int $sum
     *
     * @return Query
     */
    private function getAddSumQuery(int $userId, int $sum): Query
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $balanceQuery = $qb->update(Balance::class, 'b')
            ->set('b.sum', 'b.sum + :sum')
            ->where('b.userId = :userId')
            ->setParameter('sum', $sum)
            ->setParameter('userId', $userId)
            ->getQuery();

        return $balanceQuery;
    }

    /**
     * @param int $userId
     * @param int $sum
     *
     * @return Query
     */
    private function getSubSumQuery(int $userId, int $sum): Query
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $balanceQuery = $qb->update(Balance::class, 'b')
            ->set('b.sum', 'b.sum - :sum')
            ->where('b.userId = :userId')
            ->setParameter('sum', $sum)
            ->setParameter('userId', $userId)
            ->getQuery();

        return $balanceQuery;
    }

    /**
     * @param int    $userId
     * @param string $uuid
     *
     * @return Query
     */
    private function getDeleteBlockedMoneyQuery(int $userId, string $uuid): Query
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $deleteBlockedMoneyQuery = $qb->delete(BlockedMoney::class, 'bm')
            ->where('bm.uuid = :uuid')
            ->andWhere('bm.userId = :userId')
            ->setParameter('uuid', $uuid)
            ->setParameter('userId', $userId)
            ->getQuery();

        return $deleteBlockedMoneyQuery;
    }
}