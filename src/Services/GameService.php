<?php

namespace App\Services;

use App\Entity\Enonce;
use App\Entity\User;
use App\Repository\LevelRepository;
use App\Repository\EnonceRepository;
use Doctrine\ORM\EntityManagerInterface;

class GameService
{
    private LevelRepository $levelRepository;
    private EnonceRepository $enonceRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(LevelRepository $levelRepository, EnonceRepository $enonceRepository, EntityManagerInterface $entityManager)
    {
        $this->levelRepository = $levelRepository;
        $this->enonceRepository = $enonceRepository;
        $this->entityManager = $entityManager;
    }

    public function getExpByEnonceId(int $enonceId): int
    {
        $enonce = $this->enonceRepository->find($enonceId);
        return $enonce ? $enonce->getXpGain() : 0;
    }

    public function compareResults($expected, $actual): bool
    {
        return trim($expected) === trim($actual);
    }

    public function getExpectedOutputForLevel(int $levelId): string
    {
        $level = $this->enonceRepository->find($levelId);

        if (!$level) {
            throw new \Exception("Level not found");
        }

        return $level->getExpectedResults();
    }

    public function getEnonceForLevel(int $levelId): Enonce
    {
        $enonce = $this->enonceRepository->find($levelId);

        if (!$enonce) {
            throw new \Exception("Level not found");
        }

        return $enonce;
    }

    public function giveRewardForLevel(int $enonceId): int
    {
        $enonce = $this->enonceRepository->find($enonceId);

        if ($enonce === null) {
            throw new \Exception("Level not found for reward");
        }
        return $enonce->getXpGain();
    }

    public function giveExperienceToUser(User $user, int $experience): void
    {

        $user->setXP($user->getXP() + $experience);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
