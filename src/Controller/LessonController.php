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
    public function showLessonByLanguage(string $language, int $lessonNumber): Response
    {
        // Validation du langage
        $allowedLanguages = ['php', 'python', 'javascript'];
        $language = strtolower($language);

        if (!in_array($language, $allowedLanguages)) {
            throw $this->createNotFoundException('Ce langage n\'existe pas.');
        }

        // Validation du numéro de leçon (1, 2 ou 3)
        if ($lessonNumber < 1 || $lessonNumber > 3) {
            throw $this->createNotFoundException('Cette leçon n\'existe pas.');
        }

        // Sélectionne le template en fonction du langage
        $template = sprintf('lesson/%s/lesson%d.html.twig', $language, $lessonNumber);

        return $this->render($template, [
            'language' => $language,
            'lessonNumber' => $lessonNumber,
        ]);
    }
}
