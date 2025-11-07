<?php

namespace App\Services;

use App\Entity\Skin;
use App\Entity\User;
use App\Entity\UserLevel;
use App\Entity\UserSkin;
use App\Repository\EnonceRepository;
use App\Repository\LevelRepository;
use App\Repository\SkinRepository;
use App\Repository\UserSkinRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    private LevelRepository $levelRepository;
    private EnonceRepository $enonceRepository;
    private SkinRepository $skinRepository;
    private UserSkinRepository $userSkinRepository;
    private EntityManagerInterface $entityManager;

    // Constantes pour la progression
    private const BASE_XP = 300;        // XP nécessaire pour passer niveau 1 → 2
    private const MULTIPLIER = 1.25;    // Multiplicateur à chaque niveau

    public function __construct(
        LevelRepository $levelRepository,
        EnonceRepository $enonceRepository,
        SkinRepository $skinRepository,
        UserSkinRepository $userSkinRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->levelRepository = $levelRepository;
        $this->enonceRepository = $enonceRepository;
        $this->skinRepository = $skinRepository;
        $this->userSkinRepository = $userSkinRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Calcule le niveau d'un joueur en fonction de son XP
     *
     * @param User $user L'utilisateur dont on veut calculer le niveau
     * @return int Le niveau actuel du joueur
     */
    public function getUserLevel(User $user): int
    {
        $userXp = $user->getXP();

        // Si l'utilisateur n'a pas d'XP, il est niveau 1
        if ($userXp < self::BASE_XP) {
            return 1;
        }

        $level = 1;
        $xpForNextLevel = self::BASE_XP;
        $totalXpNeeded = 0;

        // Boucle jusqu'à ce que l'XP totale nécessaire dépasse l'XP du joueur
        while ($totalXpNeeded + $xpForNextLevel <= $userXp) {
            $totalXpNeeded += $xpForNextLevel;
            $level++;
            $xpForNextLevel = (int) round($xpForNextLevel * self::MULTIPLIER);
        }

        return $level;
    }

    /**
     * Calcule l'XP nécessaire pour atteindre un niveau donné
     *
     * @param int $targetLevel Le niveau cible
     * @return int L'XP totale nécessaire pour atteindre ce niveau
     */
    public function getXpForLevel(int $targetLevel): int
    {
        if ($targetLevel <= 1) {
            return 0;
        }

        $totalXp = 0;
        $xpForNextLevel = self::BASE_XP;

        for ($level = 1; $level < $targetLevel; $level++) {
            $totalXp += $xpForNextLevel;
            $xpForNextLevel = (int) round($xpForNextLevel * self::MULTIPLIER);
        }

        return $totalXp;
    }

    /**
     * Calcule l'XP restante avant le prochain niveau
     *
     * @param User $user L'utilisateur
     * @return array ['current_level' => int, 'xp_for_next_level' => int, 'xp_progress' => int, 'xp_remaining' => int]
     */
    public function getProgressToNextLevel(User $user): array
    {
        $currentLevel = $this->getUserLevel($user);
        $currentXp = $user->getXP();
        $xpForCurrentLevel = $this->getXpForLevel($currentLevel);
        $xpForNextLevel = $this->getXpForLevel($currentLevel + 1);

        $xpNeededForNext = $xpForNextLevel - $xpForCurrentLevel;
        $xpProgress = $currentXp - $xpForCurrentLevel;
        $xpRemaining = $xpForNextLevel - $currentXp;

        return [
            'current_level' => $currentLevel,
            'xp_for_next_level' => $xpNeededForNext,
            'current_xp' => $xpProgress,
            'xp_remaining' => $xpRemaining,
            'progress_percentage' => round(($xpProgress / $xpNeededForNext) * 100)
        ];
    }

    /**
     * Récupère tous les skins débloqués pour un utilisateur
     * 
     * @param User $user L'utilisateur
     * @return Skin[] Tableau des skins débloqués
     */
    public function getUnlockedSkins(User $user): array
    {
        // Utilise le repository UserSkin pour récupérer les skins débloqués
        $userSkins = $this->userSkinRepository->findUnlockedSkinsForUser($user->getId());

        // Extrait les objets Skin de chaque UserSkin
        return array_map(fn(UserSkin $userSkin) => $userSkin->getSkin(), $userSkins);
    }

    /**
     * Aide pour vérifier si un objet Skin est déjà dans le tableau.
     * @deprecated Cette méthode n'est plus utilisée avec le nouveau système UserSkin
     */
    private function isSkinAlreadyAdded(Skin $newSkin, array $currentSkins): bool
    {
        foreach ($currentSkins as $existingSkin) {
            if ($existingSkin->getId() === $newSkin->getId()) {
                return true;
            }
        }
        return false;
    }


    public function initializeStartingLevels(User $user): void
    {
        $startingLevels = $this->levelRepository->findBy(['number' => 1]);

        foreach ($startingLevels as $level) {
            $userLevel = new UserLevel();
            $userLevel->setUser($user);
            $userLevel->setLevel($level);
            $userLevel->setScore(0);
            $userLevel->setCompleted(false);

            $this->entityManager->persist($userLevel);
        }
    }

    /**
     * Initialise les skins par défaut pour un nouvel utilisateur
     * Chaque skin "unlocked_skin = true" dans la table skin sera débloqué pour l'utilisateur
     *
     * @param User $user Le nouvel utilisateur
     */
    public function initializeDefaultSkins(User $user): void
    {
        // Récupère tous les skins
        $allSkins = $this->skinRepository->findAll();

        foreach ($allSkins as $skin) {
            $userSkin = new UserSkin();
            $userSkin->setUser($user);
            $userSkin->setSkin($skin);

            // Si le skin est marqué comme unlocked globalement, on le débloque pour cet utilisateur
            $userSkin->setUnlocked($skin->isUnlockedSkin());

            // Si le skin est marqué comme current globalement, on le met en current pour cet utilisateur
            $userSkin->setIsCurrent($skin->isCurrent());

            $this->entityManager->persist($userSkin);
        }

        // Pas besoin de flush ici, ça sera fait par le controller qui appelle cette méthode
    }
}
