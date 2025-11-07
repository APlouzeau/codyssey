<?php

namespace App\Command;

use App\Entity\UserSkin;
use App\Repository\SkinRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:migrate-user-skins',
    description: 'Migre les données de skins globaux vers des skins par utilisateur',
)]
class MigrateUserSkinsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private SkinRepository $skinRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Migration des skins utilisateurs');

        // Récupérer tous les utilisateurs
        $users = $this->userRepository->findAll();
        $io->info(sprintf('Nombre d\'utilisateurs trouvés : %d', count($users)));

        // Récupérer tous les skins
        $skins = $this->skinRepository->findAll();
        $io->info(sprintf('Nombre de skins trouvés : %d', count($skins)));

        $totalCreated = 0;

        foreach ($users as $user) {
            $io->section(sprintf('Traitement de l\'utilisateur : %s (ID: %d)', $user->getPseudo(), $user->getId()));

            foreach ($skins as $skin) {
                // Vérifier si ce UserSkin existe déjà
                $existingUserSkin = $this->entityManager->getRepository(UserSkin::class)
                    ->findOneBy(['user' => $user, 'skin' => $skin]);

                if ($existingUserSkin) {
                    $io->text(sprintf('  - Skin "%s" déjà existant pour cet utilisateur', $skin->getFileName()));
                    continue;
                }

                // Créer un nouveau UserSkin
                $userSkin = new UserSkin();
                $userSkin->setUser($user);
                $userSkin->setSkin($skin);

                // Si le skin était "unlocked" globalement, on le débloque pour cet utilisateur
                $userSkin->setUnlocked($skin->isUnlockedSkin());

                // Si le skin était "current" globalement, on le met en current pour cet utilisateur
                $userSkin->setIsCurrent($skin->isCurrent());

                $this->entityManager->persist($userSkin);
                $totalCreated++;

                $io->text(sprintf(
                    '  ✓ Skin "%s" créé (unlocked: %s, current: %s)',
                    $skin->getFileName(),
                    $userSkin->isUnlocked() ? 'oui' : 'non',
                    $userSkin->isCurrent() ? 'oui' : 'non'
                ));
            }
        }

        // Flush une seule fois à la fin
        $this->entityManager->flush();

        $io->success(sprintf('Migration terminée ! %d UserSkin créés.', $totalCreated));

        return Command::SUCCESS;
    }
}
