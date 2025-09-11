<?php

namespace App\Repository\Matchfunding;

use App\Entity\Matchfunding\MatchCall;
use App\Entity\Matchfunding\MatchStrategy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MatchStrategy>
 */
class MatchStrategyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MatchStrategy::class);
    }

    /**
     * @return MatchStrategy[] Returns an array of ranked MatchStrategy objects
     */
    public function findByCall(MatchCall $call): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.call = :val')
            ->setParameter('val', $call)
            ->orderBy('m.ranking', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    //    public function findOneBySomeField($value): ?MatchStrategy
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
