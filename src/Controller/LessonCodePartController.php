<?php

namespace App\Controller;

use App\Entity\LessonCodePart;
use App\Form\LessonCodePartType;
use App\Repository\LessonCodePartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/lesson/code/part')]
final class LessonCodePartController extends AbstractController
{
    #[Route(name: 'app_lesson_code_part_index', methods: ['GET'])]
    public function index(LessonCodePartRepository $lessonCodePartRepository): Response
    {
        return $this->render('lesson_code_part/index.html.twig', [
            'lesson_code_parts' => $lessonCodePartRepository->findAll(),
        ]);
    }

    #[Route('/admin/new', name: 'app_lesson_code_part_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $lessonCodePart = new LessonCodePart();
        $form = $this->createForm(LessonCodePartType::class, $lessonCodePart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($lessonCodePart);
            $entityManager->flush();

            return $this->redirectToRoute('app_lesson_code_part_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('lesson_code_part/new.html.twig', [
            'lesson_code_part' => $lessonCodePart,
            'form' => $form,
        ]);
    }

    #[Route('/admin/{id}', name: 'app_lesson_code_part_show', methods: ['GET'])]
    public function show(LessonCodePart $lessonCodePart): Response
    {
        return $this->render('lesson_code_part/show.html.twig', [
            'lesson_code_part' => $lessonCodePart,
        ]);
    }

    #[Route('/admin/{id}/edit', name: 'app_lesson_code_part_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, LessonCodePart $lessonCodePart, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LessonCodePartType::class, $lessonCodePart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_lesson_code_part_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('lesson_code_part/edit.html.twig', [
            'lesson_code_part' => $lessonCodePart,
            'form' => $form,
        ]);
    }

    #[Route('/admin/{id}', name: 'app_lesson_code_part_delete', methods: ['POST'])]
    public function delete(Request $request, LessonCodePart $lessonCodePart, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$lessonCodePart->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($lessonCodePart);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_lesson_code_part_index', [], Response::HTTP_SEE_OTHER);
    }
}
