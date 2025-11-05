<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserLevel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserLevel>
 */
class UserLevelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserLevel::class);
    }

    /**
     * Récupère la progression d'un utilisateur sur un niveau spécifique
     */
    public function findUserProgress(int $userId, int $levelId): ?UserLevel
    {
        return $this->createQueryBuilder('ul')
            ->where('ul.user = :userId')
            ->andWhere('ul.level = :levelId')
            ->setParameter('userId', $userId)
            ->setParameter('levelId', $levelId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Récupère tous les niveaux complétés par un utilisateur
     */
    public function findCompletedLevels(int $userId): array
    {
        return $this->createQueryBuilder('ul')
            ->where('ul.user = :userId')
            ->andWhere('ul.completed = true')
            ->setParameter('userId', $userId)
            ->orderBy('ul.completedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcule le pourcentage de complétion d'un utilisateur
     */
    public function getCompletionPercentage(int $userId): float
    {
        $totalLevels = $this->getEntityManager()
            ->getRepository(\App\Entity\Level::class)
            ->count([]);

        $completedLevels = $this->count([
            'user' => $userId,
            'completed' => true
        ]);

        return $totalLevels > 0 ? ($completedLevels / $totalLevels) * 100 : 0;
    }

    /**
     * Récupère tous les niveaux pour lesquels un utilisateur a un score enregistré
     * (peu importe si complété ou non, tant qu'il y a un score > 0)
     * 
     * @return UserLevel[] Retourne les objets UserLevel avec les relations Level chargées
     */
    public function findLevelsWithScore(User $user): array
    {
        $userId = $user->getId();
        return $this->createQueryBuilder('ul')
            ->innerJoin('ul.level', 'l')
            ->innerJoin('l.language', 'lang')

            ->addSelect('l')
            ->addSelect('lang')

            ->where('ul.user = :userId')
            ->andWhere('ul.completed = true')
            ->setParameter('userId', $userId)

            ->orderBy('lang.name', 'ASC')
            ->addOrderBy('l.number', 'ASC')

            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère uniquement les entités Level pour lesquelles l'utilisateur a un score
     * 
     * @return \App\Entity\Level[] Retourne directement les objets Level
     */
    public function findLevelsOnlyWithScore(int $userId): array
    {
        return $this->createQueryBuilder('ul')
            ->select('l')
            ->innerJoin('ul.level', 'l')
            ->where('ul.user = :userId')
            ->andWhere('ul.score > 0')
            ->setParameter('userId', $userId)
            ->orderBy('ul.completedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
