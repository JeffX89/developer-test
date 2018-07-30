<?php

namespace App\Repository;

use App\Entity\Supporter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Supporter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Supporter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Supporter[]    findAll()
 * @method Supporter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupporterRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Supporter::class);
    }

//    /**
//     * @return Supporter[] Returns an array of Supporter objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Supporter
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
