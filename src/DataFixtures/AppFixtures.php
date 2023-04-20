<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(
        ObjectManager $manager,
    ): void
    {
        $campus1 = new Campus();
        $campus1->setNom('Campus de Rennes');
        $manager->persist($campus1);

        $campus2 = new Campus();
        $campus2->setNom('Campus de Saint-Herblain');
        $manager->persist($campus2);
        $manager->flush();

        $participants = [
            Participant::fromData(
                'arthur@tutu.net',
                'arthur',
                'arthur',
                'Chevalier',
                'Arthur',
                '0612345678',
                false,
                true,
                $campus1
            ),
            Participant::fromData(
                'admin1@sortir.com',
                'admin1',
                'admin1',
                'Un',
                'Admin',
                '0987654321',
                true,
                true,
                $campus2
            ),
            Participant::fromData(
                'rick@grimes.net',
                'rick',
                'rick',
                'Grimes',
                'Rick',
                '0912345678',
                false,
                false,
                $campus2
            ),
            Participant::fromData(
                'admin2@sortir.com',
                'admin2',
                'admin2',
                'Deux',
                'Admin',
                '0198765432',
                true,
                false,
                $campus2
            )
        ];

        foreach ($participants as $participant) {
            $participant->setMotPasse(
                $this->passwordHasher->hashPassword(
                    $participant,
                    $participant->getPassword()
                )
            );
            $manager->persist($participant);
        }
        $manager->flush();

        $ville1 = (new Ville())
            ->setNom('Rennes')
            ->setCodePostal('35000');
        $manager->persist($ville1);

        $ville2 = (new Ville())
            ->setNom('Saint-Herblain')
            ->setCodePostal('44800');
        $manager->persist($ville2);

        $manager->flush();

        $lieu1 = (new Lieu())
            ->setNom('Place de Bretagne')
            ->setRue('Place de Bretagne')
            ->setLatitude(48.10972269529868)
            ->setLongitude(-1.6837500043603484)
            ->setVille($ville1);
        $manager->persist($lieu1);

        $lieu2= (new Lieu())
            ->setNom('Mairie de Saint-Herblain')
            ->setRue('2 Rue de l\'Hôtel de ville')
            ->setLatitude(47.21095300149682)
            ->setLongitude(-1.648743991576481)
            ->setVille($ville2);
        $manager->persist($lieu2);

        $manager->flush();

        $etats = [
            (new Etat())->setLibelle(Etat::$CREEE),
            (new Etat())->setLibelle(Etat::$OUVERTE),
            (new Etat())->setLibelle(Etat::$CLOTUREE),
            (new Etat())->setLibelle(Etat::$EN_COURS),
            (new Etat())->setLibelle(Etat::$PASSEE),
            (new Etat())->setLibelle(Etat::$ANNULEE),
            (new Etat())->setLibelle(Etat::$ARCHIVEE),
        ];
        foreach ($etats as $etat) {
            $manager->persist($etat);
        }
        $manager->flush();

        $sorties = [
        (new Sortie())
            ->setNom('Marathon')
            ->setCampus($campus1)
            ->setDuree(5*60)
            ->setEtat($etats[0])
            ->setDateHeureDebut(new \DateTime('2023-04-28'))
            ->setDateLimiteInscription(new \DateTime('2023-04-26'))
            ->setNbInscriptionsMax(20)
            ->setOrganisateur($participants[0])
            ->setInfosSortie('Marathon de Bretagne, rendez-vous Place de Bretagne')
            ->setLieu($lieu1)
            ->addParticipant($participants[0])
            ->addParticipant($participants[1]),
        (new Sortie())
            ->setNom('Pêche')
            ->setCampus($campus1)
            ->setDuree(5*60)
            ->setEtat($etats[1])
            ->setDateHeureDebut(new \DateTime('2023-04-29'))
            ->setDateLimiteInscription(new \DateTime('2023-04-27'))
            ->setNbInscriptionsMax(10)
            ->setOrganisateur($participants[1])
            ->setInfosSortie('Concours de Pêche, rendez-vous Place de Bretagne')
            ->setLieu($lieu1)
            ->addParticipant($participants[0])
            ->addParticipant($participants[1])
            ->addParticipant($participants[2])
            ->addParticipant($participants[3]),
        (new Sortie())
            ->setNom('Pique-nique')
            ->setCampus($campus2)
            ->setDuree(8*24*60)
            ->setEtat($etats[4])
            ->setDateHeureDebut(new \DateTime('2023-04-01'))
            ->setDateLimiteInscription(new \DateTime('2023-03-30'))
            ->setNbInscriptionsMax(20)
            ->setOrganisateur($participants[2])
            ->setInfosSortie('Pique-nique au Parc du Val de Chézine, départ depuis la Mairie')
            ->setLieu($lieu2)
            ->addParticipant($participants[0])
            ->addParticipant($participants[1])
            ->addParticipant($participants[2]),
        (new Sortie())
            ->setNom('Randonnée')
            ->setCampus($campus2)
            ->setDuree(4*60)
            ->setEtat($etats[1])
            ->setDateHeureDebut(new \DateTime('2023-04-29'))
            ->setDateLimiteInscription(new \DateTime('2023-04-28'))
            ->setNbInscriptionsMax(25)
            ->setOrganisateur($participants[3])
            ->setInfosSortie('Randonnée marche rapide, rdv Mairie')
            ->setLieu($lieu2)
            ->addParticipant($participants[1])
        ];
        foreach ($sorties as $sortie) {
            $manager->persist($sortie);
        }
        $manager->flush();




    }
}
