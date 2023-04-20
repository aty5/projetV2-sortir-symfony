<?php

namespace App\Data;

use App\Entity\Campus;

class Filtre
{

    private ?string $nom ='';
    private ?Campus $campus = null;
    private ?\DateTime $dateDebutRecherche;
    private ?\DateTime $dateFinRecherche;
    private ?bool $organisateur = false;
    private ?bool $inscrit = false;
    private ?bool $pasInscrit = false;
    private ?bool $sortiesPassees = false;

    /**
     * @return string
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * @param string $nom
     */
    public function setNom(?string $nom): void
    {
        $this->nom = $nom;
    }

    /**
     * @return string
     */
    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    /**
     * @param string $campus
     */
    public function setCampus(?Campus $campus): void
    {
        $this->campus = $campus;
    }

    /**
     * @return \DateTime
     */
    public function getDateDebutRecherche(): ?\DateTime
    {
        return $this->dateDebutRecherche;
    }

    /**
     * @param \DateTime $dateDebutRecherche
     */
    public function setDateDebutRecherche(?\DateTime $dateDebutRecherche): void
    {
        $this->dateDebutRecherche = $dateDebutRecherche;
    }

    /**
     * @return \DateTime
     */
    public function getDateFinRecherche(): ?\DateTime
    {
        return $this->dateFinRecherche;
    }

    /**
     * @param \DateTime $dateFinRecherche
     */
    public function setDateFinRecherche(?\DateTime $dateFinRecherche): void
    {
        $this->dateFinRecherche = $dateFinRecherche;
    }

    /**
     * @return bool
     */
    public function isOrganisateur(): ?bool
    {
        return $this->organisateur;
    }

    /**
     * @param bool $organisateur
     */
    public function setOrganisateur(?bool $organisateur): void
    {
        $this->organisateur = $organisateur;
    }

    /**
     * @return bool
     */
    public function isInscrit(): ?bool
    {
        return $this->inscrit;
    }

    /**
     * @param bool $inscrit
     */
    public function setInscrit(?bool $inscrit): void
    {
        $this->inscrit = $inscrit;
    }

    /**
     * @return bool
     */
    public function isPasInscrit(): ?bool
    {
        return $this->pasInscrit;
    }

    /**
     * @param bool $pasInscrit
     */
    public function setPasInscrit(?bool $pasInscrit): void
    {
        $this->pasInscrit = $pasInscrit;
    }

    /**
     * @return bool
     */
    public function isSortiesPassees(): ?bool
    {
        return $this->sortiesPassees;
    }

    /**
     * @param bool $sortiesPassees
     */
    public function setSortiesPassees(?bool $sortiesPassees): void
    {
        $this->sortiesPassees = $sortiesPassees;
    }




}