<?php

namespace App\Repository;

use App\Entity\ActiveJob;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ActiveJob[]    findAll()
 */
class ActiveJobRepository extends JobRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ActiveJob::class);
    }
    
    public function findAllIndexedById()
    {
        return $this->createQueryBuilder('o','o.id')
            ->where('o.replacedBy is null')
            ->getQuery()
            ->getResult();
    }
    public function findAll(Type $var = null)
    {
        return $this->createQueryBuilder('o')
            ->where('o.replacedBy is null')
            ->getQuery()
            ->getResult();
    }
}
