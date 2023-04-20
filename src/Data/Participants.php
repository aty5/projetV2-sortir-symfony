<?php

namespace App\Data;

use App\Entity\Participant;
use Doctrine\Common\Collections\ArrayCollection;

class Participants {
    private ?ArrayCollection $participants = null;

    public function setParticipants(?ArrayCollection $participants): void
    {
        $this->participants = $participants;
    }

    public function getParticipants(): ?ArrayCollection {
        return $this->participants;
    }
}