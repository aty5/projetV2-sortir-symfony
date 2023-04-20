<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation\Uploadable;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
#[Uploadable]
class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $urlImage = null;

    #[ORM\Column]
    private ?int $tailleImage = null;

    #[UploadableField(mapping: 'profil_images', fileNameProperty: 'urlImage', size: 'tailleImage')]
    private ?File $fichierImage = null;

    #[ORM\OneToMany(mappedBy: 'imageProfil', targetEntity: Participant::class)]
    private Collection $participants;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrlImage(): ?string
    {
        return $this->urlImage;
    }

    public function setUrlImage(?string $urlImage): self
    {
        $this->urlImage = $urlImage;

        return $this;
    }

    public function getTailleImage(): ?int
    {
        return $this->tailleImage;
    }

    public function setTailleImage(int $tailleImage): self
    {
        $this->tailleImage = $tailleImage;

        return $this;
    }

    public function getFichierImage(): ?File
    {
        return $this->fichierImage;
    }

    public function setFichierImage(?File $fichierImage): self
    {
        $this->fichierImage = $fichierImage;

        return $this;
    }

    /**
     * @return Collection<int, Participant>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
            $participant->setImageProfil($this);
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): self
    {
        if ($this->participants->removeElement($participant)) {
            // set the owning side to null (unless already changed)
            if ($participant->getImageProfil() === $this) {
                $participant->setImageProfil(null);
            }
        }

        return $this;
    }





}
