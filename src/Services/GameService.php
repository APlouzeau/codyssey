<?php

namespace App\Services;

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

    public function compareResults($expected, $actual): bool
    {
        return trim($expected) === trim($actual);
    }
}
