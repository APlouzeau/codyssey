<?php

namespace App\Controller;

use App\Services\GameService;
use App\Services\PistonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GameController extends AbstractController
{
    public function __construct(
        private readonly GameService $gameService,
        private readonly PistonService $pistonService
    ) {}

    #[Route('/game', name: 'app_game')]
    public function index(): Response
    {
        return $this->render('game/game.html.twig', [
            'controller_name' => 'GameController',
            'experience' => $this->gameService->getExpByLevelId(),
        ]);
    }

    #[Route('/game/submit', name: 'app_game_submit', methods: ['POST'])]
    public function submitCode(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        try {
            $codeRequest = $this->pistonService->createCodeRequest(
                $data['code'],
                $data['language']
            );

            $apiResponse = $this->pistonService->controlCodeWithPiston($codeRequest);

            $expectedOutput = "Hello, World!\n";
            $actualOutput = $apiResponse['run']['stdout'];
            $isSuccess = $this->gameService->compareResults($expectedOutput, $actualOutput);

            return $this->render('game/gameResult.html.twig', [
                'expected' => $expectedOutput,
                'prompt' => $data['code'],
                'result' => $actualOutput,
                'success' => $isSuccess,
                'stderr' => $apiResponse['run']['stderr'] ?? null,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de l\'exécution',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
