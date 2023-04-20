<?php

namespace App\Command;

use App\Service\EtatUpdater;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'sortie:etat:update',
    description: 'Met à jour les états des sorties en fonction de la date et du nombre d\'inscription',
)]
class SortieEtatUpdateCommand extends Command
{
    private EtatUpdater $etatUpdater;

    public function __construct(
        EtatUpdater $etatUpdater
    ) {
        $this->etatUpdater = $etatUpdater;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Cette commande vous permet de mettre à jour l\'état des sorties. 
            Elle doit idéalement être exécutée périodiquement ainsi qu\'à chaque chargement de la liste des sorties.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->etatUpdater->updateEtatsRegardingCurrentDateTime();

        $io->success('La mise à jour de l\'état des sorties a été effectuée.');

        return Command::SUCCESS;
    }
}
