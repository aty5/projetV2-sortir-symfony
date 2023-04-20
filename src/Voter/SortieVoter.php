<?php

namespace App\Voter;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SortieVoter extends Voter
{

    private static string $ANNULER = 'ANNULER';
    private static string $PUBLIER = 'PUBLIER';
    private static string $INSCRIRE = 'INSCRIRE';
    private static string $DESINSCRIRE = 'DESINSCRIRE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::$ANNULER, self::$PUBLIER, self::$INSCRIRE, self::$DESINSCRIRE])) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof Participant) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $subject is a Post object, thanks to `supports()`
        /** @var Sortie $sortie */
        $sortie = $subject;

        return match ($attribute) {
            self::$ANNULER => $this->peutAnnuler($sortie, $user),
            self::$PUBLIER => $this->peutPublier($sortie, $user),
            self::$INSCRIRE => $this->peutSinscrire($sortie, $user),
            self::$DESINSCRIRE => $this->peutSeDesinscrire($sortie, $user),

            default => throw new \LogicException('This code should not be reached!')
        };
    }


    private function peutAnnuler(Sortie $sortie, Participant $user): bool
    {
        // if they can edit, they can view
        if (($sortie->getOrganisateur() === $user || $user->isAdministrateur())
            && in_array($sortie->getEtat()->getLibelle(), [Etat::$OUVERTE, Etat::$CLOTUREE])) {
            return true;
        }
        return false;

    }

    private function peutPublier(Sortie $sortie, Participant $user): bool
    {
        // if they can edit, they can view
        if ($sortie->getOrganisateur() === $user && $sortie->getEtat()->getLibelle() === Etat::$CREEE) {
            return true;
        }

        return false;
    }

    private function peutSinscrire(Sortie $sortie, Participant $user): bool
    {

        if (in_array($sortie, $user-> getInscriptions()->toArray()) || $sortie->getEtat()->getLibelle() !== Etat::$OUVERTE ) {
            return false;
        }

        return true;
    }

    private function peutSeDesinscrire(Sortie $sortie, Participant $user): bool
    {
        if (in_array($sortie, $user-> getInscriptions()->toArray())
            && in_array($sortie->getEtat()->getLibelle(), [Etat::$OUVERTE, Etat::$CLOTUREE])) {
            return true;
        }

        return false;
    }

}

