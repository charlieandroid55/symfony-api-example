<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create:user',
    description: 'Load initial user.',
)]
class LoadDataCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $userPasswordHasherInterface,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $user = new User(['ROLE_ADMIN'], 'admin@example.com');
            $user->setName('Admin');
            $user->setLastname('System');
            $user->setEnabled(true);
            $user->setLocale('ES');

            $password = $this->userPasswordHasherInterface->hashPassword($user, '123456789');
            $user->setPassword($password);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $io->success('Admin user created successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error creating admin user: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }

}
