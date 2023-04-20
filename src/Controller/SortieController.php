<?php

namespace App\Controller;

use App\Data\Filtre;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\FiltreType;
use App\Form\LieuType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Service\EtatUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sorties', name: 'sortie_')]
class SortieController extends AbstractController
{
    #[Route('', name: 'liste')]
    public function liste(SortieRepository $repository,ParticipantRepository $participantRepository, Request $request, EtatUpdater $etatUpdater): Response
    {
        $etatUpdater->updateEtatsRegardingCurrentDateTime();
        $moi= $participantRepository->findOneBy(['mail'=> $this->getUser()->getUserIdentifier()]);
        $filtrage = new Filtre();
        $sortiesForm = $this->createForm(FiltreType::class, $filtrage);
        $sortiesForm->handleRequest($request);

        if ($sortiesForm->isSubmitted()) {
            $sorties = $repository->findWithParameters(
                $filtrage->getNom(),
                $filtrage->getCampus(),
                $filtrage->getDateDebutRecherche(),
                $filtrage->getDateFinRecherche(),
                $filtrage->isOrganisateur(),
                $filtrage->isInscrit(),
                $filtrage->isPasInscrit(),
                $filtrage->isSortiesPassees(),
                $moi
            );
       } else {
            $sorties = $repository->findListe($moi);
        }

        return $this->render('sortie/index.html.twig',
            [
            'sortiesForm' => $sortiesForm->createView(),
                'sorties'=>$sorties,
                'moi'=>$moi
        ]);
    }

    #[Route('/ajouter', name: 'ajouter')]
    #[Route('/{id}/modifier', name: 'modifier', requirements: ['id' => '\d+'])]
    public function ajouter_sortie(Sortie $sortie = null, Request $request, EntityManagerInterface $entityManager): Response
    {
        $lieu = new Lieu();
        $message = "";

        if ($sortie !== null) {
            $titre = "Modifier la sortie " . $sortie->getNom();
            if ($this->getUser()->getUserIdentifier() !== $sortie ->getOrganisateur()->getUserIdentifier()) {
                $this->addFlash('error', 'Vous n\'êtes pas l\'organisateur de cette sortie');
                return $this->redirectToRoute('main_home');
            }
        } else {
            $titre = "Ajouter une nouvelle sortie";
            $sortie = new Sortie();
        }

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $lieuForm = $this->createForm(LieuType::class, $lieu);
        $sortieForm -> handleRequest($request);
        $lieuForm -> handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid())
        {
            $sortie->setOrganisateur($this->getUser());
            $sortie->setInfosSortie(htmlspecialchars(strip_tags($sortie->getInfosSortie())));
            $etat = $entityManager->getRepository(Etat::class);

            if ($sortieForm -> get('enregistrer')->isClicked()) {
                $etat = $etat->findOneBy(['libelle'=>Etat::$CREEE]);
                $sortie->setEtat($etat);
                $message = 'Votre sortie à été enregistrer';
            }

            if ($sortieForm -> get('publier')->isClicked()) {
                $etat = $etat->findOneBy(['libelle'=>Etat::$OUVERTE]);
                $sortie->setEtat($etat);
                $message = "Votre sortie à été publier";
            }

            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash('success', $message);
            return $this->redirectToRoute('main_home');
        }

        if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {
            $entityManager->persist($lieu);
            $entityManager->flush();
            $message = "Nouveau lieu ajouter";
            $this->addFlash('success', $message);
        }

        return $this->render('sortie/create.html.twig', [
            'titre'=>$titre,
            'sortieForm'=>$sortieForm->createView(),
            'lieuForm' => $lieuForm->createView()
        ]);
    }


    #[Route('/{id}', name: 'details', requirements: ['id' => '\d+'])]
    public function details(
        int $id,
        SortieRepository $sortieRepository,
    ): Response {
        $sortie = $sortieRepository->find($id);
        if ($sortie == null) {
            throw $this->createNotFoundException('Aucune sortie ne correspond à l\'identifiant ' . $id);
        }
        if ($sortie->getOrganisateur()->getUserIdentifier() != $this->getUser()->getUserIdentifier()
                && in_array($sortie->getEtat()->getLibelle(), [Etat::$CREEE, Etat::$ARCHIVEE], true)) {
            throw $this->createAccessDeniedException('Vous n\'avez pas l\'autorisation de consulter les détails de cette sortie.');
        }
        return $this->render('sortie/details.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/{id}/inscription', name: 'inscription', requirements: ['id' => '\d+'])]
    public function inscription(
        int $id,
        ParticipantRepository $participantRepository,
        SortieRepository $sortieRepository,
        EtatRepository $etatRepository,
    ): Response {
        $sortie = $sortieRepository->find($id);
        if ($sortie == null) {
            throw $this->createNotFoundException('Aucune sortie ne correspond à l\'identifiant ' . $id);
        }
        /** @var Participant $moi */
        $moi = $this->getUser();
        if (!$this->isGranted('INSCRIRE',$sortie)) {
            $this->addFlash('error', 'Vous ne pouvez pas vous inscrire à cette sortie car vous êtes déjà inscrit.e ou les inscriptions sont closes.');
        }
        else {
            $sortie->addParticipant($moi);
            // Si le nombre maximal de participants est atteint, clôture des inscriptions
            if (count($sortie->getParticipants()) >= $sortie->getNbInscriptionsMax()) {
                $sortie->setEtat($etatRepository->findOneBy(['libelle' => Etat::$CLOTUREE]));
            }
            $sortieRepository->save($sortie, true);
            $this->addFlash('success', 'Vous êtes à présent inscrit.e à la sortie '.$sortie->getNom().'.');
        }
        return $this->redirectToRoute('sortie_liste');
    }

    #[Route('/{id}/desinscription', name: 'desinscription', requirements: ['id' => '\d+'])]
    public function desinscription(
        int $id,
        ParticipantRepository $participantRepository,
        SortieRepository $sortieRepository,
        EtatRepository $etatRepository,
    ): Response {
        $sortie = $sortieRepository->find($id);
        if ($sortie == null) {
            throw $this->createNotFoundException('Aucune sortie ne correspond à l\'identifiant ' . $id);
        }
        $moi = $participantRepository->findOneBy(['mail' => $this->getUser()->getUserIdentifier()]);

        if (!$this->isGranted('DESINSCRIRE', $sortie)) {
            $this->addFlash('error', 'Impossible de vous désinscrire de cette sortie car vous n\'y étiez pas inscrit.e ou elle a déjà commencé.');
        }

         else {

            $sortie->removeParticipant($moi);
            // Si le nombre de participants ET la date permettent de rouvrir les inscriptions on change l'état.
            if ($sortie->getEtat()->getLibelle() === Etat::$CLOTUREE
                    && count($sortie->getParticipants()) < $sortie->getNbInscriptionsMax()
                    && (new \DateTime()) < $sortie->getDateLimiteInscription()) {
                $sortie->setEtat($etatRepository->findOneBy(['libelle' => Etat::$OUVERTE]));
            }
            $sortieRepository->save($sortie, true);
            $this->addFlash('success', 'Vous vous êtes désisté.e de la sortie '.$sortie->getNom().'.');
        }

        return $this->redirectToRoute('sortie_liste');
    }

    #[Route('/{id}/annulation', name: 'annulation', requirements: ['id' => '\d+'])]
    public function annulation(
        int $id,
        SortieRepository $sortieRepository,
        EtatRepository $etatRepository,
        Request $request,
    ): Response {
        $sortie = $sortieRepository->find($id);
        if ($sortie == null) {
            throw $this->createNotFoundException('Aucune sortie ne correspond à l\'identifiant ' . $id);
        }

        if(!$this->isGranted('ANNULER',$sortie)) {
            $this->addFlash('error', 'Vous n\'avez pas l\'autorisation d\'annuler cette sortie car vous n\'en êtes pas l\'organisateur.trice ou son état ne le permet pas.');
            return $this->redirectToRoute('sortie_liste');
        } elseif ($request->request->get('enregistrer') != null) {
            if ($request->request->get('motif-annulation') == null) {
                $this->addFlash('error', 'Le motif de l\'annulation doit être renseigné.');
                return $this->redirect($request->getUri());
            }
            $sortie->setInfosSortie($sortie->getInfosSortie()."<br><br> ---SORTIE ANNULÉE---<br>Motif : ".htmlspecialchars(strip_tags($_POST['motif-annulation'])));
            $sortie->setEtat($etatRepository->findOneBy(['libelle' => Etat::$ANNULEE]));
            $sortieRepository->save($sortie, true);
            $this->addFlash('success', 'La sortie '.$sortie->getNom().' a été annulée.');
            return $this->redirectToRoute('sortie_liste');
        } elseif ($request->request->get('annuler') != null) {
            return $this->redirectToRoute('sortie_liste');
        }


        return $this->render('sortie/annulation.html.twig', [
            'sortie' => $sortie
        ]);
    }

    #[Route('/{id}/publication', name: 'publication', requirements: ['id'=>'\d+'])]
    public function publication(
        int              $id,
        SortieRepository $sortieRepository,
        EtatRepository   $etatRepository,
    ): Response
    {
        $sortie = $sortieRepository->find($id);
        if ($sortie == null) {
            throw $this->createNotFoundException('Aucune sortie ne correspond à l\'identifiant ' . $id);
        }
        if (!$this->isGranted('PUBLIER',$sortie)) {
            $this->addFlash('error', 'Vous n\'avez pas l\'autorisation de publier cette sortie car vous n\'en êtes pas l\'organisateur.trice ou parce qu\'elle a déjà été publiée');
            return $this->redirectToRoute('sortie_liste');
        }

        else {
            $sortie->setEtat($etatRepository->findOneBy(['libelle' => Etat::$OUVERTE]));
            $sortieRepository->save($sortie, true);
            $this->addFlash('success', 'La sortie ' . $sortie->getNom() . ' a été publiée avec succès !');
        }
        return $this->redirectToRoute('sortie_liste');
    }

}
