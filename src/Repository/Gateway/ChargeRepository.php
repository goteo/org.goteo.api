<?php

namespace App\Repository\Gateway;

use App\Entity\Accounting\Accounting;
use App\Entity\Gateway\Charge;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Charge>
 *
 * @method Charge|null find($id, $lockMode = null, $lockVersion = null)
 * @method Charge|null findOneBy(array $criteria, array $orderBy = null)
 * @method Charge[]    findAll()
 * @method Charge[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChargeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Charge::class);
    }

    /**
     * @return Charge[] Returns an array of Charge objects
     */
    public function findByOriginAndTarget(Accounting $origin, Accounting $target): array
    {
        return $this->createQueryBuilder('g')
            ->join('g.checkout', 'c', Join::WITH, 'c.id = g.checkout')
            ->where('c.origin = :origin')
            ->andWhere('g.target = :target')
            ->setParameter('origin', $origin)
            ->setParameter('target', $target)
            ->orderBy('g.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
