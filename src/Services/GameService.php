<?php

namespace App\Services;

use App\Entity\Enonce;
use App\Entity\Level;
use App\Entity\User;
use App\Repository\LevelRepository;
use App\Repository\EnonceRepository;
use App\Repository\LanguageRepository;
use Doctrine\ORM\EntityManagerInterface;

class GameService
{
    private LevelRepository $levelRepository;
    private EnonceRepository $enonceRepository;
    private EntityManagerInterface $entityManager;
    private LanguageRepository $languageRepository;
    private const MAX_POINTS = 7;
    private const MID_POINTS = 4;
    private const MIN_POINTS = 1;

    public function __construct(LevelRepository $levelRepository, EnonceRepository $enonceRepository, EntityManagerInterface $entityManager, LanguageRepository $languageRepository)
    {
        $this->levelRepository = $levelRepository;
        $this->enonceRepository = $enonceRepository;
        $this->entityManager = $entityManager;
        $this->languageRepository = $languageRepository;
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

    // public function getExpectedOutputForLevel(int $levelId): string
    // {
    //     $level = $this->enonceRepository->find($levelId);

    //     if (!$level) {
    //         throw new \Exception("Level not found");
    //     }

    //     return $level->getExpectedResults();
    // }

    public function getExpectedOutputForLevel(int $levelId): string
    {
        // CORRECTION : Utiliser LevelRepository pour trouver le Level
        $level = $this->levelRepository->find($levelId); 
        
        if (!$level) {
            throw new \Exception("Level (ID: {$levelId}) not found");
        }

        // Le Level doit avoir une relation vers l'Enonce
        $enonce = $level->getEnonce(); // Assurez-vous que cette méthode existe sur l'entité Level

        if (!$enonce) {
            throw new \Exception("Enonce not linked to Level (ID: {$levelId})");
        }

        // Retourner le résultat attendu de l'Enonce
        return $enonce->getExpectedResults();
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


    public function getEnonceForLanguageAndNumber(string $language, int $number): Level
    {
        // Utilisez findBy pour récupérer tous les énoncés correspondant aux critères.
        // findBy retourne toujours un tableau d'objets (ou un tableau vide si rien n'est trouvé).
        $verifLanguage = $this->languageRepository->findOneBy(['name' => $language]);
        if (!$verifLanguage) {
            throw new \Exception("Langue '{$language}' non trouvée.");
        }

        $verifNumber = $this->levelRepository->findBy(['number' => $number]);
        if (!$verifNumber) {
            throw new \Exception("Niveau numéro '{$number}' non trouvé.");
        }

        // maintenant je dois croiser les deux
        $verifNumber = array_filter($verifNumber, function (Level $level) use ($verifLanguage) {
            return $level->getLanguage() === $verifLanguage;
        });
        if (empty($verifNumber)) {
            throw new \Exception("Aucun niveau trouvé pour la langue '{$language}' et le numéro '{$number}'.");
        }

        // dans la liste des number, j'en prends un aléatoirement
        $randomLevel = $verifNumber[array_rand($verifNumber)];
        // dd($verifLanguage, $verifNumber, $randomLevel);

        return $randomLevel;
    }

    public function promptIsFalse(int $currentLifes): int
    {
        if ($currentLifes > 0) {
            return $currentLifes - 1;
        }
        return 0;
    }

    public function calculateScore(int $currentLifes,): int
    {
        return match (true) {
            $currentLifes > self::MAX_POINTS => 3,
            $currentLifes > self::MID_POINTS => 2,
            $currentLifes > 0 => 1,
            default => 0,
        };
    }
}
