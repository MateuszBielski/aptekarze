<?php

namespace App\Repository;

use App\Entity\AbstrMember;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AbstrMember|null find($id, $lockMode = null, $lockVersion = null)
 * @method AbstrMember|null findOneBy(array $criteria, array $orderBy = null)
 * @method AbstrMember[]    findAll()
 * @method AbstrMember[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AbstrMemberRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AbstrMember::class);
    }

    // /**
    //  * @return AbstrMember[] Returns an array of AbstrMember objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AbstrMember
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
