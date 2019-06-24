<?php

namespace App\Repository;

use App\Entity\MemberHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MemberHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method MemberHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method MemberHistory[]    findAll()
 * @method MemberHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MemberHistoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MemberHistory::class);
    }

    // /**
    //  * @return MemberHistory[] Returns an array of MemberHistory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MemberHistory
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findAllIndexedById()
    {
        return $this->createQueryBuilder('o','o.id')
            ->getQuery()
            ->getResult();
    }
}
