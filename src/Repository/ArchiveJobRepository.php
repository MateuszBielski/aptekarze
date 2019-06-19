<?php

namespace App\Repository;

use App\Entity\ArchiveJob;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ArchiveJob|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArchiveJob|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArchiveJob[]    findAll()
 * @method ArchiveJob[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArchiveJobRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ArchiveJob::class);
    }

    // /**
    //  * @return ArchiveJob[] Returns an array of ArchiveJob objects
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
    public function findOneBySomeField($value): ?ArchiveJob
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
