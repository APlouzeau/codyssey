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

    /**
     * Récupère TOUS les niveaux avec number = 1 (pour chaque langage)
     * Permet ensuite de faire un random côté PHP pour varier les niveaux
     */
    public function getFirstsLevels(): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.number = 1')
            ->orderBy('l.language', 'ASC')
            ->addOrderBy('l.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère UN niveau aléatoire avec number = 1 pour un langage donné
     */
    public function getRandomFirstLevelByLanguage(int $languageId): ?Level
    {
        $levels = $this->createQueryBuilder('l')
            ->where('l.number = 1')
            ->andWhere('l.language = :languageId')
            ->setParameter('languageId', $languageId)
            ->getQuery()
            ->getResult();

        if (empty($levels)) {
            return null;
        }

        // Random côté PHP
        return $levels[array_rand($levels)];
    }
}
