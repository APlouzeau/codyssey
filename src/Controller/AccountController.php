<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangeEmailFormType;
use App\Form\ChangePasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Form\FormError;

final class AccountController extends AbstractController
{
    #[Route('/account/security', name: 'app_account_security')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function security(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Formulaire de modification d'email (mappé à l'entité)
        $emailForm = $this->createForm(ChangeEmailFormType::class, $user);
        $emailForm->handleRequest($request);

        if ($emailForm->isSubmitted() && $emailForm->isValid()) {
            $currentPassword = $emailForm->get('currentPassword')->getData();
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $emailForm->get('currentPassword')->addError(new FormError('Mot de passe actuel invalide.'));
            } else {
                // L'e-mail est déjà mis à jour via le mapping du formulaire
                $entityManager->flush();
                $this->addFlash('success', 'Votre e-mail a été mis à jour.');
                return $this->redirectToRoute('app_account_security');
            }
        }

        // Formulaire de modification de mot de passe (non mappé)
        $passwordForm = $this->createForm(ChangePasswordFormType::class);
        $passwordForm->handleRequest($request);

        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $currentPassword = $passwordForm->get('currentPassword')->getData();
            $newPassword = $passwordForm->get('newPassword')->getData();

            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $passwordForm->get('currentPassword')->addError(new FormError('Mot de passe actuel invalide.'));
            } else {
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                $entityManager->flush();
                $this->addFlash('success', 'Votre mot de passe a été mis à jour.');
                return $this->redirectToRoute('app_account_security');
            }
        }

        return $this->render('account/security.html.twig', [
            'emailForm' => $emailForm->createView(),
            'passwordForm' => $passwordForm->createView(),
        ]);
    }
}