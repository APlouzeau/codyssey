<?php

namespace App\Controller;

use App\Repository\UserLevelRepository;
use App\Services\GameService;
use App\Services\MapService;
use App\Services\PistonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MapController extends AbstractController
{

    public function __construct(
        private readonly GameService $gameService,
        private readonly PistonService $pistonService,
        private readonly EntityManagerInterface $entityManager,
        private readonly MapService $mapService,
        private readonly UserLevelRepository $userLevelRepository
    ) {}
    #[Route('/map', name: 'app_map')]
    public function index(): Response
    {
        $user = $this->getUser();
        $levelTerminated = $this->userLevelRepository->findLevelsWithScore($user);
        dd($levelTerminated);
        return $this->render('map/index.html.twig', [
            'controller_name' => 'MapController',
            'level_available' => $levelTerminated,
        ]);
    }
}
