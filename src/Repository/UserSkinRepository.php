<?php

namespace App\Repository;

use App\Entity\UserSkin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserSkin>
 */
class UserSkinRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSkin::class);
    }

    /**
     * Trouve tous les skins débloqués pour un utilisateur
     *
     * @return UserSkin[]
     */
    public function findUnlockedSkinsForUser(int $userId): array
    {
        return $this->createQueryBuilder('us')
            ->andWhere('us.user = :userId')
            ->andWhere('us.unlocked = true')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve le skin actuellement sélectionné pour un utilisateur et un avatar
     */
    public function findCurrentSkinForUserAndAvatar(int $userId, int $avatarId): ?UserSkin
    {
        return $this->createQueryBuilder('us')
            ->innerJoin('us.skin', 's')
            ->andWhere('us.user = :userId')
            ->andWhere('s.avatar = :avatarId')
            ->andWhere('us.isCurrent = true')
            ->setParameter('userId', $userId)
            ->setParameter('avatarId', $avatarId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Vérifie si un utilisateur a débloqué un skin spécifique
     */
    public function hasUserUnlockedSkin(int $userId, int $skinId): bool
    {
        $userSkin = $this->findOneBy([
            'user' => $userId,
            'skin' => $skinId,
            'unlocked' => true
        ]);

        return $userSkin !== null;
    }
}
