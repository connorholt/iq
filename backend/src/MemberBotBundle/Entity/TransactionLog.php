<?php

declare(strict_types = 1);

namespace MemberBotBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TransactionLog.
 *
 * @ORM\Table(name="transaction_log")
 * @ORM\Entity(repositoryClass="MemberBotBundle\Repository\TransactionLogRepository")
 */
class TransactionLog
{
    public const TYPE_INCOME = 1;

    public const TYPE_WITHDRAW = 2;

    public const TYPE_LOCK = 3;

    public const TYPE_TRANSFER = 4;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="type", type="integer")
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="sum", type="bigint")
     */
    private $sum;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="details", type="json")
     */
    private $details;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type.
     *
     * @param int $type
     *
     * @return TransactionLog
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set sum.
     *
     * @param int $sum
     *
     * @return TransactionLog
     */
    public function setSum($sum)
    {
        $this->sum = $sum;

        return $this;
    }

    /**
     * Get sum.
     *
     * @return int
     */
    public function getSum()
    {
        return $this->sum;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return TransactionLog
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set details.
     *
     * @param string $details
     *
     * @return TransactionLog
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }

    /**
     * Get details.
     *
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }
}
