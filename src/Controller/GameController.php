<?php

namespace App\Controller;

use App\Entity\Tips;
use App\Entity\User;
use App\Form\GamePromptFormType;
use App\Repository\LevelRepository;
use App\Repository\UserLevelRepository;
use App\Services\GameService;
use App\Services\PistonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GameController extends AbstractController
{
    public function __construct(
        private readonly GameService $gameService,
        private readonly PistonService $pistonService,
        private readonly EntityManagerInterface $entityManager,
        private readonly LevelRepository $levelRepository,
        private readonly UserLevelRepository $userLevelRepository
    ) {}

    /*     #[Route('/game', name: 'app_game')]
    public function index(): Response
    {
        return $this->render('game/game.html.twig', [
            'controller_name' => 'GameController',
            'experience' => $this->gameService->getExpByLevelId(),
        ]);
    } */

    // public function submitCode(Request $request, string $language, int $number): Response
    // {
    //     $form = $this->createForm(GamePromptFormType::class);
    //     $form->handleRequest($request);

    //     $level = $this->gameService->getEnonceForLanguageAndNumber($language, $number);
    //     $enonce = $level->getEnonce();

    //     $tips = $enonce->getTips()->toArray();

    //     // dd($tips[0]->getName());
    //     $tip = $tips[0]->getName();
    //     // dd($tip);

    //     $tips = $tipsCollection->map(function (Tips $tip) {
    //          return [
    //              'id' => $tip->getId(),
    //              'name' => $tip->getName(), // <-- Cible l'attribut contenant le contenu
    //              ];
    //      })->toArray();

    //     return $this->render('game/game.html.twig', [
    //         'gameForm' => $form->createView(),
    //         'level' => $level,
    //         'tips' => $tip,
    //         'levelNumber' => $number,
    //         'language' => $language,
    //         'title' => $enonce->getTitle(),
    //         'enonce' => $enonce->getContent(),
    //         'result' => $enonce->getExpectedResults(),
    //         'lifes' => $enonce->getLifeNumber(),
    //     ]);
    // }

    #[Route('/game/{language}/{number}', requirements: ['language' => '\w+', 'number' => '\d+'], name: 'app_game_submit', methods: ['GET'])]
    public function submitCode(Request $request, string $language, int $number): Response
    {
        $form = $this->createForm(GamePromptFormType::class);
        $form->handleRequest($request);

        $level = $this->gameService->getEnonceForLanguageAndNumber($language, $number);
        $enonce = $level->getEnonce();
        $xpGain = $enonce->getXpGain();


        $tipsCollection = $enonce->getTips();

        // Solution propre : Mapper la collection Doctrine vers un tableau PHP simple
        $tips = $tipsCollection->map(function (Tips $tip) {
            return [
                'id' => $tip->getId(),
                'name' => $tip->getName(), // <-- On récupère le contenu de l'indice (clé 'name')
            ];
        })->toArray(); // Conversion en tableau PHP natif



        return $this->render('game/game.html.twig', [
            'gameForm' => $form->createView(),
            'level' => $level,
            'tips' => $tips, // Tableau PHP simple
            'levelNumber' => $number,
            'language' => $language,
            'xpGain' => $xpGain,
            'title' => $enonce->getTitle(),
            'enonce' => $enonce->getContent(),
            'result' => $enonce->getExpectedResults(),
            'lifes' => $enonce->getLifeNumber(),
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

    #[Route('/game/victory', name: 'app_game_victory', methods: ['GET'])]
    public function gameVictory(Request $request): Response
    {
        $result = $request->getSession()->get('game_result');

        if (!$result) {
            return $this->redirectToRoute('app_game_submit');
        }

        $experience = $this->gameService->getExpByEnonceId(1);
        $user = $this->getUser();
        if ($user instanceof User) {
            $this->gameService->giveExperienceToUser($user, $experience);
        }

        // Supprimer les résultats de la session
        $request->getSession()->remove('game_result');

        return $this->render('game/gameVictory.html.twig', [
            'experience' => $experience,
            'result' => $result
        ]);
    }

    /**
     * API endpoint pour React : Exécute le code du joueur et retourne le résultat en JSON
     */
    #[Route('/api/game/execute', name: 'api_game_execute', methods: ['POST'])]
    public function fetchGamePromptExecute(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Validation des données
            if (!isset($data['language'], $data['code'], $data['level_id'], $data['current_lifes'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Paramètres manquants (language, code, level_id, current_lifes requis)'
                ], 400);
            }

            $languageName = $data['language'];
            $code = $data['code'];
            $currentLifes = (int) $data['current_lifes'];
            $levelId = (int) $data['level_id'];


            // Vérifier que le level existe
            $level = $this->levelRepository->find($levelId);
            if (!$level) {
                return $this->json([
                    'success' => false,
                    'error' => 'Level introuvable'
                ], 404);
            }

            $enonceId = $level->getEnonce()->getId();

            // Exécution du code via Piston
            $codeRequest = $this->pistonService->createCodeRequest($code, $languageName);
            $apiResponse = $this->pistonService->controlCodeWithPiston($codeRequest);

            // Comparaison des résultats
            $expectedOutput = $this->gameService->getExpectedOutputForLevel($levelId);
            $actualOutput = $apiResponse['run']['stdout'] ?? 'Pas de sortie';
            $isSuccess = $this->gameService->compareResults($expectedOutput, $actualOutput);

            // Calcul des nouvelles vies
            $newLifes = $isSuccess ? $currentLifes : $this->gameService->promptIsFalse($currentLifes);

            // Attribution des récompenses si succès
            if ($isSuccess) {
                $user = $this->getUser();
                if ($user instanceof User) {
                    $experience = $this->gameService->getExpByEnonceId($enonceId);
                    $this->gameService->giveExperienceToUser($user, $experience);
                    $score = $this->gameService->calculateScore($currentLifes);
                    $this->userLevelRepository->setUserLevelCompleted($user, $level, $score);
                }
            }

            // Retour JSON
            return $this->json([
                'isSuccess' => $isSuccess,
                'expectedOutput' => $expectedOutput,
                'actualOutput' => $actualOutput,
                'code' => $code,
                'stderr' => $apiResponse['run']['stderr'] ?? null,
                'executionTime' => $apiResponse['run']['cpu_time'] ?? null,
                'newLifes' => $newLifes,
                'enonceId' => $enonceId,
                'experienceGained' => $isSuccess ? $experience : 0
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de l\'exécution',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
