<?php

namespace App\Repository;

use App\Entity\AccountingFunding;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AccountingFunding>
 *
 * @method AccountingFunding|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountingFunding|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountingFunding[]    findAll()
 * @method AccountingFunding[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountingFundingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountingFunding::class);
    }

//    /**
//     * @return AccountingFunding[] Returns an array of AccountingFunding objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AccountingFunding
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
