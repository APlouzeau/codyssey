<?php

namespace App\Controller;

use App\Entity\Tips;
use App\Form\TipsType;
use App\Repository\TipsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/tips')]
final class TipsController extends AbstractController
{
    #[Route(name: 'app_tips_index', methods: ['GET'])]
    public function index(TipsRepository $tipsRepository): Response
    {
        return $this->render('tips/index.html.twig', [
            'tips' => $tipsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_tips_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tip = new Tips();
        $form = $this->createForm(TipsType::class, $tip);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tip);
            $entityManager->flush();

            return $this->redirectToRoute('app_tips_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tips/new.html.twig', [
            'tip' => $tip,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_tips_show', methods: ['GET'])]
    public function show(Tips $tip): Response
    {
        return $this->render('tips/show.html.twig', [
            'tip' => $tip,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_tips_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tips $tip, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TipsType::class, $tip);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_tips_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tips/edit.html.twig', [
            'tip' => $tip,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_tips_delete', methods: ['POST'])]
    public function delete(Request $request, Tips $tip, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tip->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($tip);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_tips_index', [], Response::HTTP_SEE_OTHER);
    }
}
