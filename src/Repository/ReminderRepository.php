<?php

namespace App\Repository;

use App\Entity\Reminder;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Reminder|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reminder|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reminder[]    findAll()
 * @method Reminder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReminderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reminder::class);
    }

    public function findRemindersWithinWeekByUser(User $user)
    {
        $from = new DateTime('today');
        $to = new DateTime('today');
        $to->modify('+1 week');

        $result = $this->createQueryBuilder('r')
            ->where('r.createdBy = :user')
            ->setParameter('user', $user->getId()->toBinary())
            ->andWhere('r.date BETWEEN :from AND :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->orderBy('r.date', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function findExceededRemindersByUser(User $user)
    {
        $today = new DateTime('today');

        $result = $this->createQueryBuilder('r')
            ->where('r.createdBy = :user')
            ->setParameter('user', $user->getId()->toBinary())
            ->andWhere('r.date < :today')
            ->setParameter('today', $today)
            ->orderBy('r.date', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function findRemindersWithDifferentStatusByUser(int $status, User $user)
    {
        $result = $this->createQueryBuilder('r')
            ->where('r.status != :status')
            ->setParameter('status', $status)
            ->andWhere('r.createdBy = :user')
            ->setParameter('user', $user->getId()->toBinary())
            ->getQuery()
            ->getResult();

        return $result;
    }
}
