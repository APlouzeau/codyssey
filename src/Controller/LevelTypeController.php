<?php

namespace App\Controller;

use App\Entity\LevelType;
use App\Form\LevelTypeType;
use App\Repository\LevelTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/level/type')]
final class LevelTypeController extends AbstractController
{
    #[Route(name: 'app_level_type_index', methods: ['GET'])]
    public function index(LevelTypeRepository $levelTypeRepository): Response
    {
        return $this->render('level_type/index.html.twig', [
            'level_types' => $levelTypeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_level_type_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $levelType = new LevelType();
        $form = $this->createForm(LevelTypeType::class, $levelType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($levelType);
            $entityManager->flush();

            return $this->redirectToRoute('app_level_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('level_type/new.html.twig', [
            'level_type' => $levelType,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_level_type_show', methods: ['GET'])]
    public function show(LevelType $levelType): Response
    {
        return $this->render('level_type/show.html.twig', [
            'level_type' => $levelType,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_level_type_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, LevelType $levelType, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LevelTypeType::class, $levelType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_level_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('level_type/edit.html.twig', [
            'level_type' => $levelType,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_level_type_delete', methods: ['POST'])]
    public function delete(Request $request, LevelType $levelType, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$levelType->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($levelType);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_level_type_index', [], Response::HTTP_SEE_OTHER);
    }
}
