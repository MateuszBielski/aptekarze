<?php

namespace App\Repository;

use App\Entity\MemberUser;
use App\Entity\Job;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MemberUserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MemberUser::class);
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    // public function findByNamePortion(string $string)
    // {
    //     return $this->createQueryBuilder('o','o.id')
    //    ->where('o.firstName LIKE :string or o.surname LIKE :string')
    //    ->setParameter('string', '%'.$string.'%')
    //    ->setMaxResults(100)
    //    ->getQuery()
    //    ->getResult();
    // }
    //rozdzielone spacją
    public function findByNamePortion(string $stringToExplode)
    {
        $string= explode(" ",$stringToExplode);
        $result = $this->createQueryBuilder('o','o.id');
       
       if (count($string) > 1) {
        $result = $result
            ->where('o.firstName LIKE :string0 or o.surname LIKE :string0')
            ->setParameter('string0', '%'.$string[0].'%')
            ->andWhere('o.firstName LIKE :string1 or o.surname LIKE :string1')
            ->setParameter('string1', '%'.$string[1].'%'); 
       } else {
        $result = $result
            ->where('o.firstName LIKE :string OR o.surname LIKE :string OR o.nrPrawaZawodu LIKE :string')
            ->setParameter('string', '%'.$stringToExplode.'%');
       }
       $result = $result
       ->setMaxResults(50)
       ->getQuery()
       ->getResult();

        return $result;
    }
    public function findAllIndexedById()
    {
        return $this->createQueryBuilder('o','o.id')
            ->orderBy('o.surname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findWithJobInRangeIndexedById(Job $job, array $ids_range)
    {
        return $this->createQueryBuilder('mu','mu.id')
        ->where("mu.id IN(:usersIds)")
        ->andWhere("mu.job = :jobPar")
        ->setParameter('usersIds', $ids_range)
        ->setParameter('jobPar', $job)
        ->getQuery()
        ->getResult();
    }

    public function findByJobIndexed(Job $job, array $ids_range = null)
    {
        return $this->createQueryBuilder('mu','mu.id')
        ->Where("mu.job = :jobPar")
        ->orWhere( "mu.id IN(:usersIds)")
        ->setParameter('jobPar', $job)
        ->setParameter('usersIds', $ids_range)
        ->getQuery()
        ->getResult();
    }
}
