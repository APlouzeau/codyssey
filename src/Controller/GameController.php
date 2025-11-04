<?php

namespace App\Controller;

use App\Service\GameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GameController extends AbstractController
{
    public function __construct(
        private readonly GameService $gameService
    ) {}

    #[Route('/game', name: 'app_game')]
    public function index(): Response
    {
        return $this->render('game/game.html.twig', [
            'controller_name' => 'GameController',
            'experience' => $this->gameService->getExpByLevelId(),
        ]);
    }
}
