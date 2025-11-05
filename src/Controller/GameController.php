<?php

namespace App\Controller;

use App\Form\GamePromptFormType;
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

    /*     #[Route('/game', name: 'app_game')]
    public function index(): Response
    {
        return $this->render('game/game.html.twig', [
            'controller_name' => 'GameController',
            'experience' => $this->gameService->getExpByLevelId(),
        ]);
    } */

    #[Route('/game', name: 'app_game_submit', methods: ['GET', 'POST'])]
    public function submitCode(Request $request): Response
    {
        $form = $this->createForm(GamePromptFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();

                // Récupérer le nom du langage depuis l'entité en minuscules
                $languageName = strtolower($data['language']->getName());

                $codeRequest = $this->pistonService->createCodeRequest(
                    $data['code'],
                    $languageName
                );

                $apiResponse = $this->pistonService->controlCodeWithPiston($codeRequest);

                $expectedOutput = $this->gameService->getExpectedOutputForLevel(1); // Récupérer la sortie attendue pour le niveau 1
                $actualOutput = $apiResponse['run']['stdout'] ?? 'Pas de sortie';
                $isSuccess = $this->gameService->compareResults($expectedOutput, $actualOutput);

                // Stocker les résultats en session pour les afficher après redirection
                $request->getSession()->set('game_result', [
                    'expected' => $expectedOutput,
                    'prompt' => $data['code'],
                    'result' => $actualOutput,
                    'success' => $isSuccess,
                    'stderr' => $apiResponse['run']['output'] ?? null,
                ]);

                // REDIRECTION au lieu de render (pour Turbo)
                return $this->redirectToRoute('app_game_result');
            } catch (\Exception $e) {
                return $this->json([
                    'error' => 'Erreur lors de l\'exécution',
                    'message' => $e->getMessage()
                ], 500);
            }
        }
        return $this->render('game/game.html.twig', [
            'gameForm' => $form->createView(),
        ]);
    }

    #[Route('/game/result', name: 'app_game_result', methods: ['GET'])]
    public function showResult(Request $request): Response
    {
        $result = $request->getSession()->get('game_result');

        if (!$result) {
            return $this->redirectToRoute('app_game_submit');
        }

        // Supprimer les résultats de la session après les avoir récupérés
        $request->getSession()->remove('game_result');

        return $this->render('game/gameResult.html.twig', $result);
    }
}
