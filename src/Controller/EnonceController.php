<?php

namespace App\Controller;

use App\Entity\Enonce;
use App\Form\EnonceType;
use App\Repository\EnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/enonce')]
final class EnonceController extends AbstractController
{
    #[Route(name: 'app_enonce_index', methods: ['GET'])]
    public function index(EnonceRepository $enonceRepository): Response
    {
        return $this->render('enonce/index.html.twig', [
            'enonces' => $enonceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_enonce_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $enonce = new Enonce();
        $form = $this->createForm(EnonceType::class, $enonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($enonce);
            $entityManager->flush();

            return $this->redirectToRoute('app_enonce_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('enonce/new.html.twig', [
            'enonce' => $enonce,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_enonce_show', methods: ['GET'])]
    public function show(Enonce $enonce): Response
    {
        return $this->render('enonce/show.html.twig', [
            'enonce' => $enonce,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_enonce_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Enonce $enonce, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EnonceType::class, $enonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_enonce_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('enonce/edit.html.twig', [
            'enonce' => $enonce,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_enonce_delete', methods: ['POST'])]
    public function delete(Request $request, Enonce $enonce, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$enonce->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($enonce);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_enonce_index', [], Response::HTTP_SEE_OTHER);
    }
}
