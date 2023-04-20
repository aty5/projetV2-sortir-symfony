<?php

namespace App\Controller;

use App\Data\Participants;
use App\Entity\Image;
use App\Entity\Participant;
use App\Form\CSVFileType;
use App\Form\ImageType;
use App\Form\ParticipantsType;
use App\Form\ParticipantType;
use App\Form\ProfilType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/participants', name: 'participant_')]
class ParticipantController extends AbstractController
{
    #[Route('/gestion', name: 'gestion')]
    public function gestion(
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $participants = new Participants();
        $participantsForm = $this->createForm(ParticipantsType::class, $participants);
        $participantsForm->handleRequest($request);

        if ($participantsForm->isSubmitted() && $participantsForm->isValid()) {
            if ($participantsForm->get('desactiver')->isClicked()) {
                foreach($participants->getParticipants() as $participant) {
                    $participant->setActif(false);
                    $entityManager->persist($participant);
                }
            } elseif ($participantsForm->get('activer')->isClicked()) {
                foreach($participants->getParticipants() as $participant) {
                    $participant->setActif(true);
                    $entityManager->persist($participant);
                }
            } elseif ($participantsForm->get('supprimer')->isClicked()) {
                foreach($participants->getParticipants() as $participant) {
                    // TODO Traiter les sorties organisées par ce participant...
                    $entityManager->remove($participant);
                }
            }

            $entityManager->flush();
            return $this->redirect($request->getUri());
        }
        return $this->render('participant/gestion.html.twig', [
            'participantsForm' => $participantsForm->createView(),
        ]);
    }

    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('participant/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/profil', name: 'profil')]
    public function profil(
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        /** @var Participant $participant */
        $participant = $this->getUser();

        $image = new Image();
        $imageForm = $this->createForm(ImageType::class, $image);
        $form = $this->createForm(ProfilType::class, $participant);
        $form->handleRequest($request);
        $imageForm->handleRequest($request);
        $motPasse = $form->get('motPasse')->getData();
        $confirmation = $form->get('confirmation')->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            if ($motPasse != null) {
                if ($motPasse == $confirmation) {
                    $participant->setMotPasse(
                    //hashPassword permet de crypter le mdp
                        $passwordHasher->hashPassword(
                        //récupère le "format" de $participant
                            $participant,
                            $motPasse,
                        ));
                    $this->addFlash('success', 'Votre mot de passe a bien été modifié.');
                } else {
                    $this->addFlash('error', 'Les deux mots de passe ne sont pas identiques.');
                    return $this->render('participant/profil.html.twig', [
                        "profilForm" => $form->createView(),
                        "imageForm" => $imageForm->createView()
                    ]);

                }

            }
            //faire persister l'info sinon elle ne part pas en bdd
            $manager->persist($participant);
            //permet d'enclencher la maj dans la bdd
            $manager->flush();
            $this->addFlash('success', 'Votre profil a bien été mis à jour.');
            //une fois terminé, renvoie sur la route app_profil avec le bon id lié à l'utilisateur
            return $this->redirectToRoute('participant_profil', ['id' => $participant->getId()]);
        }
        if($imageForm->isSubmitted()&& $imageForm->isValid()){
            $manager->persist($image);
            $manager->flush();
            $participant->setImageProfil($image);
            $manager->persist($participant);
            $manager->flush();
        }

        return $this->render('participant/profil.html.twig', [
            "profilForm" => $form->createView(),
            "participant" => $participant,
            "imageForm" => $imageForm->createView()
        ]);
    }
    #[Route('/{id}', name: 'details', requirements: ['id' => '\d+'])]
    public function details(
        int $id,
        ParticipantRepository $participantRepository,
        Request $request
    ): Response
    {
        $participant = $participantRepository->find($id);
        return $this->render('participant/detailsParticipant.html.twig', [
            'participant' => $participant,
            'precedent'=>$request->headers->get('referer')
        ]);
    }

    #[Route('/ajouter', name: 'ajouter')]
    public function ajouter(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        ParticipantRepository $participantRepository,
        EntityManagerInterface $entityManager,
        CampusRepository $campusRepository,
        ValidatorInterface $validator,
    ): Response {
        $participant = new Participant();
        $participantForm = $this->createForm(ParticipantType::class, $participant);
        $csvFileForm = $this->createForm(CSVFileType::class);

        $participantForm->handleRequest($request);
        if ($participantForm->isSubmitted() && $participantForm->isValid()) {
            $participant->setMotPasse(
                $passwordHasher->hashPassword($participant, $participant->getMotPasse())
            );
            $participantRepository->save($participant, true);
            $this->addFlash('success', 'Le participant a été enregistré.');
            return $this->redirect($request->getUri());
        }

        $csvFileForm->handleRequest($request);
        if ($csvFileForm->isSubmitted() && $csvFileForm->isValid()) {
            $csvFile = $csvFileForm['csv_file']->getData();
            $separator = $csvFileForm['separator']->getData();
            $messageOK = 'Participants ajoutés : ';
            $messageFail = 'Erreurs rencontrées : ';
            foreach (explode("\n", $csvFile->getContent()) as $csvParticipant) {
                if (trim($csvParticipant) != '') {
                    $pa = array_map('trim', explode($separator, $csvParticipant));
                    if (array_key_exists(5, $pa)) {
                        $pa[5] = $campusRepository->findOneBy(['nom' => $pa[5]]);
                    }
                    $participant = Participant::fromCSV($pa, $separator);
                    $errors = $validator->validate($participant);
                    if (count($errors) > 0) {
                        $messageFail .= (string)$errors;
                    } else {
                        $participant->setMotPasse(
                            $passwordHasher->hashPassword($participant, $participant->getMotPasse())
                        );
                        $participantRepository->save($participant);
                        $messageOK .= $participant->getPrenom(). ' ' . $participant->getNom() . ', ';
                    }

                }
            }
            $entityManager->flush();
            $this->addFlash('success', $messageOK);
            if ($messageFail !== 'Erreurs rencontrées : ') {
                $this->addFlash('error', $messageFail);
            }
            return $this->redirect($request->getUri());
        }

        return $this->render('participant/ajouter.html.twig', [
            'participantForm' => $participantForm->createView(),
            'csvFileForm' => $csvFileForm->createView(),
        ]);
    }

}
