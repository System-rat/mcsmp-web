<?php

namespace App\Repository;

use App\Entity\UserAPIKey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserAPIKey|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserAPIKey|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserAPIKey[]    findAll()
 * @method UserAPIKey[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserAPIKeyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserAPIKey::class);
    }

    // /**
    //  * @return UserAPIKey[] Returns an array of UserAPIKey objects
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
    public function findOneBySomeField($value): ?UserAPIKey
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
