<?php

namespace App\Services;

use App\Entity\User;
use App\Entity\Level;
use App\Entity\UserLevel;
use App\Repository\EnonceRepository;
use App\Repository\LevelRepository;
use Doctrine\ORM\EntityManagerInterface;

class MapService
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
}
