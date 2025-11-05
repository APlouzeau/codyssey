<?php

namespace App\Controller;

use App\Entity\Lesson;
use App\Form\LessonType;
use App\Repository\LanguageRepository;
use App\Repository\LessonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/lesson')]
final class LessonController extends AbstractController
{

    public function __construct(
        private readonly LanguageRepository $languageRepository,
        private readonly LessonRepository $lessonRepository,
    )
    {
    }

    #[Route('/home', name: 'app_lesson_home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->render('lesson/home.html.twig');
    }

    #[Route(name: 'app_lesson_index', methods: ['GET'])]
    public function index(LessonRepository $lessonRepository): Response
    {
        return $this->render('lesson/index.html.twig', [
            'lessons' => $lessonRepository->findAll(),
        ]);
    }

    #[Route('/admin/new', name: 'app_lesson_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $lesson = new Lesson();
        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($lesson);
            $entityManager->flush();

            return $this->redirectToRoute('app_lesson_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('lesson/new.html.twig', [
            'lesson' => $lesson,
            'form' => $form,
        ]);
    }

    #[Route('/admin/{id}', name: 'app_lesson_show', methods: ['GET'])]
    public function show(Lesson $lesson): Response
    {
        return $this->render('lesson/show.html.twig', [
            'lesson' => $lesson,
        ]);
    }

    #[Route('/admin/{id}/edit', name: 'app_lesson_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Lesson $lesson, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_lesson_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('lesson/edit.html.twig', [
            'lesson' => $lesson,
            'form' => $form,
        ]);
    }

    #[Route('/admin/{id}', name: 'app_lesson_delete', methods: ['POST'])]
    public function delete(Request $request, Lesson $lesson, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$lesson->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($lesson);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_lesson_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Affiche une leçon spécifique pour un langage
     * Route: /lesson/{language}/{lessonNumber}
     */
    #[Route('/{language}/{lessonNumber}', name: 'app_lesson_show_by_language', methods: ['GET'])]
    public function showLessonByLanguage(string $language, int $lessonNumber): Response {
        // Validation du langage

        $languagesBdd = $this->languageRepository->findAll();


        // je veux vérifier pour chaque language de ma bdd si la language passer 
        // en paramètre correspond à un des language de ma bdd
        // Si ce n'est pas le cas je revoie une erreur
      
        $nameLanguagesBdd = [];
        foreach ($languagesBdd as $languageBdd) {
            // je souhaite convertir ma liste d'objet language en une liste de string
            $nameLanguagesBdd[] = strtolower($languageBdd->getName());
        }

        if (!in_array($language, $nameLanguagesBdd)) {
            throw $this->createNotFoundException('Ce langage n\'existe pas.');
        }

        $languageEntity = $this->languageRepository->findOneBy(['name' => $language]);


        $lessons = $languageEntity->getLessons();

        // Récupère la leçon depuis la base de données
        $actualLessons = null;

        foreach ($lessons as $lesson) {
            if ($lesson->getNumber() === $lessonNumber) {
                $actualLessons = $lesson;
                break;
            }
        }

        // dd($actualLessons);
        // dd($actualLessons->getContent());

        // Si la leçon existe en BDD avec du contenu JSON, on utilise le template dynamique

        $lessonJson = json_decode($actualLessons->getContent(), true, 512, JSON_THROW_ON_ERROR);
        if ($actualLessons && $actualLessons->getContent()) {
            return $this->render('lesson/dynamic_lesson.html.twig', [
                'language' => $language,
                'lessonNumber' => $lessonNumber,
                'lesson' => $actualLessons,
                'content' => $lessonJson,
            ]);
        }

        // Sinon, on utilise les templates statiques (fallback)
        $template = sprintf('lesson/%s/lesson%d.html.twig', $language, $lessonNumber);

        return $this->render($template, [
            'language' => $language,
            'lessonNumber' => $lessonNumber,
        ]);
    }
}
