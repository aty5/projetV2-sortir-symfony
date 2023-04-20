<?php

namespace App\Voter;

use App\Entity\Participant;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ParticipantVoter extends Voter {

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [])) {
            return false;
        }
        if (!$subject instanceof Participant) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof Participant) {
            return false;
        }

        /** @var Participant $participant */
        $participant = $subject;

        return match($attribute) {
            default => throw new \LogicException('Code inatteignable -- Erreur de codage')
        };
    }
}