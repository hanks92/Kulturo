<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\UserPlantInventory;
use App\Repository\UserPlantInventoryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:add-plant',
    description: 'Ajoute des plantes à un utilisateur.',
)]
class AddPlantCommand extends Command
{
    private EntityManagerInterface $em;
    private UserRepository $userRepository;
    private UserPlantInventoryRepository $inventoryRepository;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository, UserPlantInventoryRepository $inventoryRepository)
    {
        parent::__construct();
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->inventoryRepository = $inventoryRepository;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('userId', InputArgument::REQUIRED, 'ID de l\'utilisateur')
            ->addArgument('plantType', InputArgument::REQUIRED, 'Type de plante')
            ->addArgument('quantity', InputArgument::REQUIRED, 'Quantité à ajouter');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userId = $input->getArgument('userId');
        $plantType = $input->getArgument('plantType');
        $quantityToAdd = (int) $input->getArgument('quantity');

        /** @var User|null $user */
        $user = $this->userRepository->find($userId);

        if (!$user) {
            $output->writeln("<error>Utilisateur $userId introuvable.</error>");
            return Command::FAILURE;
        }

        $inventory = $this->inventoryRepository->findOneBy([
            'userApp' => $user,
            'plantType' => $plantType,
        ]);

        if (!$inventory) {
            $inventory = new UserPlantInventory();
            $inventory->setUserApp($user);
            $inventory->setPlantType($plantType);
            $inventory->setQuantity(0);
            $this->em->persist($inventory);
        }

        $inventory->setQuantity($inventory->getQuantity() + $quantityToAdd);
        $this->em->flush();

        $output->writeln("<info>$quantityToAdd $plantType ajouté à l'utilisateur $userId !</info>");

        return Command::SUCCESS;
    }
}
