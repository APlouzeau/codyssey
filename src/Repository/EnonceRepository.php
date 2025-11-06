<?php

namespace App\Repository;

use App\Entity\Enonce;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Enonce>
 */
class EnonceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Enonce::class);
    }

    //    /**
    //     * @return Enonce[] Returns an array of Enonce objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Enonce
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function getExperienceByEnonceId(int $enonceId): ?int
    {
        $enonce = $this->find($enonceId);
        return $enonce ? $enonce->getXpGain() : null;
    }

    public function getResultsByEnonceId(int $enonceId): ?string
    {
        $enonce = $this->find($enonceId);
        return $enonce ? $enonce->getExpectedResults() : null;
    }
}
