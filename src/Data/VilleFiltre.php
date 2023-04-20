<?php

namespace App\Data;

use App\Entity\Ville;

class VilleFiltre
{
    private ?string $nom="";

    /**
     * @return string|null
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * @param string|null $nom
     * @return void
     */
    public function setNom(?string $nom):void
    {
        $this->nom = $nom;
    }
}