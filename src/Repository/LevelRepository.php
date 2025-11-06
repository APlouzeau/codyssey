<?php

namespace App\Repository;

use App\Entity\Level;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Level>
 */
class LevelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Level::class);
    }

    //    /**
    //     * @return Level[] Returns an array of Level objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Level
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function getExperienceByLevelId(int $levelId): ?int
    {
        $level = $this->find($levelId);
        return $level?->getScore();
    }

    public function getNextLevelForUser(Level $currentLevel): Level|string|null
    {
        $currentLevelNumber = $currentLevel->getNumber() + 1;
        if ($currentLevelNumber > 6) {
            return "Aucun niveau suivant disponible.";
        }
        return $this->createQueryBuilder('l')
            ->where('l.language = :language')
            ->andWhere('l.number = :number')
            ->setParameter('language', $currentLevel->getLanguage())
            ->setParameter('number', $currentLevelNumber)
            ->orderBy('l.number', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
