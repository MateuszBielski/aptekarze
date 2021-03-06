<?php

namespace App\Repository;

use App\Entity\Contribution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Contribution|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contribution|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contribution[]    findAll()
 * @method Contribution[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContributionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Contribution::class);
    }

    // /**
    //  * @return Contribution[] Returns an array of Contribution objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Contribution
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
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
    public function findAllIndexedByIdOrderBy(string $field, string $direct)
    {
        return $this->createQueryBuilder('o','o.id')
            ->orderBy($field, $direct)
            ->getQuery()
            ->getResult();
    }
    public function findByUserIdIn(array $usersId)
    {
        return $this->createQueryBuilder('o','o.id')
        ->leftJoin('o.myUser','user')
        ->where("user.id IN(:usersIds)")
        ->setParameter('usersIds',array_values($usersId))
        ->getQuery()
        ->getResult();
    }

    public function findByDateIndexedById(\Datetime $date)
    {

        $older = new \DateTime($date->format("Y-m-d")." 23:59:59");
    
        return $this->createQueryBuilder("e")
            ->where('e.paymentDate <= :older')
            ->setParameter('older', $older )
            ->orderBy('e.paymentDate', 'DESC')
            ->setMaxResults(30)
            ->getQuery()
            ->getResult();
    }
}
