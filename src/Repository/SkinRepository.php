<?php

namespace App\Repository;

use App\Entity\Skin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Skin>
 */
class SkinRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Skin::class);
    }

    /**
     * Récupère tous les skins pour un avatar donné
     *
     * @return Skin[]
     */
    public function findByAvatar(int $avatarId): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.avatar = :avatarId')
            ->setParameter('avatarId', $avatarId)
            ->orderBy('s.fileName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère un skin par son nom de fichier
     */
    public function findOneByFileName(string $fileName): ?Skin
    {
        return $this->createQueryBuilder('s')
            ->where('s.fileName = :fileName')
            ->setParameter('fileName', $fileName)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
