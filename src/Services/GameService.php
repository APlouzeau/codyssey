<?php

namespace App\Service;

use App\Repository\LevelRepository;
use App\Repository\EnonceRepository;

class GameService
{
    private EnonceRepository $enonceRepository;

    public function __construct(LevelRepository $levelRepository, EnonceRepository $enonceRepository)
    {
        $this->enonceRepository = $enonceRepository;
    }

    public function getExpByLevelId(): string
    {
        $level = $this->enonceRepository->getExperienceByEnonceId(1);
        return "Experience gained: $level!";
    }

    public function callApiCode($data): string
    {
        // Simulate API call
        return "API response for data: " . json_encode($data);
    }

    public function compareResults($expected, $actual): bool
    {
        return trim($expected) === trim($actual);
    }
}
