<?php

namespace App\Controller;

use App\Entity\UserSkin;
use App\Repository\LevelRepository;
use App\Repository\UserLevelRepository;
use App\Repository\UserSkinRepository;
use App\Services\GameService;
use App\Services\MapService;
use App\Services\PistonService;
use App\Services\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class MapController extends AbstractController
{

    public function __construct(
        private readonly GameService $gameService,
        private readonly PistonService $pistonService,
        private readonly EntityManagerInterface $entityManager,
        private readonly MapService $mapService,
        private readonly UserLevelRepository $userLevelRepository,
        private readonly LevelRepository $levelRepository,
        private readonly UserService $userService,
        private readonly UserSkinRepository $userSkinRepository,
        // appelle la variable SKINS_DIRECTORY du fichier .env.local
        #[Autowire(env: 'SKINS_DIRECTORY')] private readonly string $skinsDirectory,
    ) {}
    #[Route('/map', name: 'app_map')]
    public function index(): Response
    {
        $user = $this->getUser();
        $userLevel = $this->userService->getUserLevel($user);
        $levelsTerminated = $this->userLevelRepository->findLevelsWithScore($user);

        // Récupère tous les premiers niveaux (pour les niveaux non commencés)
        $nextLevels = $this->levelRepository->getFirstsLevels();

        $xpNeeded = $this->userService->getProgressToNextLevel($user);

        // Ajoute les niveaux suivants pour chaque niveau terminé
        foreach ($levelsTerminated as $levelProgress) {
            $currentLevel = $levelProgress->getLevel();
            $nextLevel = $this->levelRepository->getNextLevelForUser($currentLevel);

            if ($nextLevel instanceof \App\Entity\Level) {
                $nextLevels[] = $nextLevel;
            }
        }

        $skins = $this->userSkinRepository->findBy([
            'user' => $user,
            'unlocked' => true,
        ]);

        $skinsWithPaths = [];
        foreach ($skins as $userSkin) {
            $skin = $userSkin->getSkin();
            $fileName = $skin->getFileName();
            $skinsWithPaths[$fileName] = $this->skinsDirectory . '/' . $fileName;
        }

        return $this->render('map/index.html.twig', [
            'skins' => $skinsWithPaths,
            'levels_terminated' => $levelsTerminated,
            'next_levels' => $nextLevels,
            'user_level' => $userLevel,
            'xp_needed' => $xpNeeded,
        ]);
    }
}
