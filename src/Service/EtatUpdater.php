<?php

namespace App\Service;

use App\Entity\Etat;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;

class EtatUpdater {
    private SortieRepository $sortieRepository;
    private EtatRepository $etatRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        SortieRepository $sortieRepository,
        EtatRepository $etatRepository,
        EntityManagerInterface $entityManager,
    ) {
        $this->sortieRepository = $sortieRepository;
        $this->etatRepository = $etatRepository;
        $this->entityManager = $entityManager;
    }

    public function updateEtatsRegardingCurrentDateTime(): void {
        // Passage si nécessaire de l'état ouvert à l'état clôturée.
        foreach ($this->sortieRepository->findOuverteACloturee() as $sortie) {
            $sortie->setEtat($this->etatRepository->findOneBy(['libelle' => Etat::$CLOTUREE]));
            $this->entityManager->persist($sortie);
        }
        $this->entityManager->flush();

        // Passage si nécessaire de l'état clôturée à l'état en cours.
        foreach ($this->sortieRepository->findClotureeAEnCours() as $sortie) {
            $sortie->setEtat($this->etatRepository->findOneBy(['libelle' => Etat::$EN_COURS]));
            $this->entityManager->persist($sortie);
        }
        $this->entityManager->flush();

        // Passage si nécessaire de l'état en cours à l'état passée.
        foreach ($this->sortieRepository->findEnCoursAPassee() as $sortie) {
            $sortie->setEtat($this->etatRepository->findOneBy(['libelle' => Etat::$PASSEE]));
            $this->entityManager->persist($sortie);
        }
        $this->entityManager->flush();

        // Passage si nécessaire de l'état passée ou annulée à l'état archivée.
        foreach ($this->sortieRepository->findPasseeOuAnnuleeAArchivee() as $sortie) {
            $sortie->setEtat($this->etatRepository->findOneBy(['libelle' => Etat::$ARCHIVEE]));
            $this->entityManager->persist($sortie);
        }
        $this->entityManager->flush();

    }
}