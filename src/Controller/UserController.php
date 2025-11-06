<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\ChangeUserFormType;
use App\Repository\UserRepository;
use App\Repository\UserSkinRepository;
use App\Repository\AvatarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Form\FormError;

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/security', name: 'app_user_security')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function security(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        UserSkinRepository $userSkinRepository,
        AvatarRepository $avatarRepository
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $originalEmail = $user->getEmail();

        // Récupérer les skins débloqués pour chaque avatar (JS, PHP, Python)
        $avatarJs = $avatarRepository->findOneBy(['name' => 'JavaScript']);
        $avatarPhp = $avatarRepository->findOneBy(['name' => 'PHP']);
        $avatarPy = $avatarRepository->findOneBy(['name' => 'Python']);

        $unlockedSkinsJs = [];
        $unlockedSkinsPhp = [];
        $unlockedSkinsPy = [];

        // Récupérer les skins débloqués pour JavaScript
        if ($avatarJs) {
            $userSkins = $userSkinRepository->findBy([
                'user' => $user,
                'unlocked' => true,
            ]);
            foreach ($userSkins as $userSkin) {
                $skin = $userSkin->getSkin();
                if ($skin->getAvatar() === $avatarJs) {
                    $fileName = $skin->getFileName();
                    // Format: 'Label affiché' => 'valeur'
                    $unlockedSkinsJs[ucfirst(str_replace('.png', '', $fileName))] = $fileName;
                }
            }
        }

        // Récupérer les skins débloqués pour PHP
        if ($avatarPhp) {
            $userSkins = $userSkinRepository->findBy([
                'user' => $user,
                'unlocked' => true,
            ]);
            foreach ($userSkins as $userSkin) {
                $skin = $userSkin->getSkin();
                if ($skin->getAvatar() === $avatarPhp) {
                    $fileName = $skin->getFileName();
                    $unlockedSkinsPhp[ucfirst(str_replace('.png', '', $fileName))] = $fileName;
                }
            }
        }

        // Récupérer les skins débloqués pour Python
        if ($avatarPy) {
            $userSkins = $userSkinRepository->findBy([
                'user' => $user,
                'unlocked' => true,
            ]);
            foreach ($userSkins as $userSkin) {
                $skin = $userSkin->getSkin();
                if ($skin->getAvatar() === $avatarPy) {
                    $fileName = $skin->getFileName();
                    $unlockedSkinsPy[ucfirst(str_replace('.png', '', $fileName))] = $fileName;
                }
            }
        }

        // Créer le formulaire avec les skins débloqués
        $form = $this->createForm(ChangeUserFormType::class, $user, [
            'unlocked_skins_js' => $unlockedSkinsJs,
            'unlocked_skins_php' => $unlockedSkinsPhp,
            'unlocked_skins_py' => $unlockedSkinsPy,
        ]);
        $form->handleRequest($request);

        // Précharger les avatars sélectionnés (GET)
        if (!$form->isSubmitted()) {
            // Récupérer les skins actuellement sélectionnés par l'utilisateur
            $currentSkinsJs = $userSkinRepository->findBy([
                'user' => $user,
                'isCurrent' => true,
            ]);

            $defaults = [
                'js' => null,
                'php' => null,
                'py' => null,
            ];

            foreach ($currentSkinsJs as $userSkin) {
                $skin = $userSkin->getSkin();
                $fn = $skin->getFileName() ?? '';
                if (str_contains($fn, 'JS')) {
                    $defaults['js'] = $fn;
                } elseif (str_contains($fn, 'PHP')) {
                    $defaults['php'] = $fn;
                } elseif (str_contains($fn, 'PY')) {
                    $defaults['py'] = $fn;
                }
            }

            if ($defaults['js']) {
                $form->get('avatar_js')->setData($defaults['js']);
            }
            if ($defaults['php']) {
                $form->get('avatar_php')->setData($defaults['php']);
            }
            if ($defaults['py']) {
                $form->get('avatar_py')->setData($defaults['py']);
            }
        }

        if ($form->isSubmitted()) {
            $hasErrors = false;

            $currentPassword = $form->get('currentPassword')->getData();
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $form->get('currentPassword')->addError(new FormError('Mot de passe actuel invalide.'));
                $hasErrors = true;
            }

            $newPassword = $form->get('newPassword')->get('first')->getData();
            if (!empty($newPassword) && strlen($newPassword) < 8) {
                $form->get('newPassword')->addError(new FormError('Votre mot de passe doit contenir au moins 8 caractères.'));
                $hasErrors = true;
            }

            $emailChanged = $user->getEmail() !== $originalEmail;
            $passwordChanged = !empty($newPassword);

            if (!$hasErrors && $form->isValid()) {
                if ($passwordChanged) {
                    $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                }

                // Mettre à jour les skins sélectionnés
                $selectedJs = $form->get('avatar_js')->getData();
                $selectedPhp = $form->get('avatar_php')->getData();
                $selectedPy = $form->get('avatar_py')->getData();
                $skinChanged = false;

                $skinRepo = $entityManager->getRepository(\App\Entity\Skin::class);

                foreach (
                    [
                        ['fileName' => $selectedJs, 'avatar' => $avatarJs],
                        ['fileName' => $selectedPhp, 'avatar' => $avatarPhp],
                        ['fileName' => $selectedPy, 'avatar' => $avatarPy],
                    ] as $selection
                ) {
                    if ($selection['fileName'] && $selection['avatar']) {
                        /** @var \App\Entity\Skin|null $skin */
                        $skin = $skinRepo->findOneBy(['file_name' => $selection['fileName']]);
                        if ($skin) {
                            // Désélectionner tous les skins de cet avatar pour cet utilisateur
                            $userSkinsForAvatar = $userSkinRepository->createQueryBuilder('us')
                                ->join('us.skin', 's')
                                ->where('us.user = :user')
                                ->andWhere('s.avatar = :avatar')
                                ->setParameter('user', $user)
                                ->setParameter('avatar', $selection['avatar'])
                                ->getQuery()
                                ->getResult();

                            foreach ($userSkinsForAvatar as $us) {
                                $us->setIsCurrent(false);
                            }

                            // Sélectionner le skin choisi
                            $userSkin = $userSkinRepository->findOneBy([
                                'user' => $user,
                                'skin' => $skin,
                            ]);

                            if ($userSkin) {
                                $userSkin->setIsCurrent(true);
                                $skinChanged = true;
                            }
                        }
                    }
                }

                $hasUpdates = $emailChanged || $passwordChanged || $skinChanged;

                if ($hasUpdates) {
                    $entityManager->flush();

                    if ($emailChanged && $passwordChanged && $skinChanged) {
                        $this->addFlash('success', 'Votre e-mail, mot de passe et avatars ont été mis à jour.');
                    } elseif ($emailChanged && $passwordChanged) {
                        $this->addFlash('success', 'Votre e-mail et votre mot de passe ont été mis à jour.');
                    } elseif ($emailChanged && $skinChanged) {
                        $this->addFlash('success', 'Votre e-mail et vos avatars ont été mis à jour.');
                    } elseif ($passwordChanged && $skinChanged) {
                        $this->addFlash('success', 'Votre mot de passe et vos avatars ont été mis à jour.');
                    } elseif ($emailChanged) {
                        $this->addFlash('success', 'Votre e-mail a été mis à jour.');
                    } elseif ($passwordChanged) {
                        $this->addFlash('success', 'Votre mot de passe a été mis à jour.');
                    } elseif ($skinChanged) {
                        $this->addFlash('success', 'Vos avatars ont été mis à jour.');
                    }

                    return $this->redirectToRoute('app_user_security');
                } else {
                    $this->addFlash('success', 'Aucune modification détectée.');
                }
            }
        }

        return $this->render('user/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/admin/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/admin/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/admin/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
