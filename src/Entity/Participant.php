<?php

namespace App\Entity;

use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[UniqueEntity(fields: ['mail'])]
#[UniqueEntity(fields: ['pseudo'])]
#[Vich\Uploadable]
class Participant implements UserInterface, PasswordAuthenticatedUserInterface, \Serializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    private ?string $mail = null;

    /**
     * @var string The hashed motPasse
     */
    #[ORM\Column]
    #[Assert\NotCompromisedPassword]
    private ?string $motPasse = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Ce champ doit être renseigné.')]
    #[Assert\Length(max: 100, maxMessage: 'Ce champ peut comporter au maximum {{ limit }} caractères.')]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Ce champ doit être renseigné.')]
    #[Assert\Length(max: 100, maxMessage: 'Ce champ peut comporter au maximum {{ limit }} caractères.')]
    private ?string $prenom = null;

    #[ORM\Column(length: 15, nullable: true)]
    #[Assert\Length(max: 15, maxMessage: 'Ce champ peut comporter au maximum {{ limit }} caractères.')]
    private ?string $telephone = null;

    #[ORM\Column]
    private ?bool $administrateur = null;

    #[ORM\Column]
    private ?bool $actif = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank(message: 'Ce champ doit être renseigné.')]
    #[Assert\Length(max: 100, maxMessage: 'Ce champ peut comporter au maximum {{ limit }} caractères.')]
    #[Assert\Regex(pattern: '/@/', message: 'Le pseudo ne doit pas contenir le caractère \'@\'.', match: false)]
    private ?string $pseudo = null;

    #[ORM\ManyToMany(targetEntity: Sortie::class, inversedBy: 'participants')]
    private Collection $inscriptions;

    #[ORM\OneToMany(mappedBy: 'organisateur', targetEntity: Sortie::class)]
    private Collection $sortiesOrganisees;

    #[ORM\ManyToOne(inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le campus du participant doit impérativement être renseigné.')]
    private ?Campus $campus = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'participants')]
    private ?Image $imageProfil = null;

    public function __construct()
    {
        $this->inscriptions = new ArrayCollection();
        $this->sortiesOrganisees = new ArrayCollection();
    }

    public static function fromCSV(array $pa, string $separator): self
    {
        return self::fromData(
            array_key_exists(0, $pa) ? $pa[0] : null,
            array_key_exists(1, $pa) ? $pa[1] : null,
            array_key_exists(6, $pa) ? $pa[6] : null,
            array_key_exists(2, $pa) ? $pa[2] : null,
            array_key_exists(3, $pa) ? $pa[3] : null,
            array_key_exists(4, $pa) ? $pa[4] : null,
            array_key_exists(7, $pa) ? (strtolower($pa[7]) == 'oui' ? true : false) : null,
            array_key_exists(8, $pa) ? (strtolower($pa[8]) == 'oui' ? true : false) : null,
            array_key_exists(5, $pa) ? $pa[5] : null
        );
    }

    public static function fromData(
        ?string $mail,
        ?string $pseudo,
        ?string $motPasse,
        ?string $nom,
        ?string $prenom,
        ?string $telephone,
        ?bool   $administrateur,
        ?bool   $actif,
        ?Campus $campus,
    ): self
    {
        return (new Participant())
            ->setMail($mail)
            ->setPseudo($pseudo)
            ->setNom($nom)
            ->setPrenom($prenom)
            ->setTelephone($telephone)
            ->setAdministrateur($administrateur)
            ->setActif($actif)
            ->setCampus($campus)
            ->setmotPasse($motPasse);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(?string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->mail;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return $this->administrateur ? ['ROLE_ADMIN'] : ['ROLE_USER'];
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->motPasse;
    }

    public function setMotPasse(?string $motPasse): ?self
    {
        $this->motPasse = $motPasse;

        return $this;
    }

    public function getMotPasse(): ?string
    {
        return $this->motPasse;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainmotPasse = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function isAdministrateur(): ?bool
    {
        return $this->administrateur;
    }

    public function setAdministrateur(?bool $administrateur): self
    {
        $this->administrateur = $administrateur;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(?bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(?string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getInscriptions(): ?Collection
    {
        return $this->inscriptions;
    }

    public function addInscription(?Sortie $sortie): self
    {
        if (!$this->inscriptions->contains($sortie)) {
            $this->inscriptions->add($sortie);
        }

        return $this;
    }

    public function removeInscription(?Sortie $sortie): self
    {
        $this->inscriptions->removeElement($sortie);

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortiesOrganisees(): Collection
    {
        return $this->sortiesOrganisees;
    }

    public function addSortiesOrganisee(Sortie $sortiesOrganisee): self
    {
        if (!$this->sortiesOrganisees->contains($sortiesOrganisee)) {
            $this->sortiesOrganisees->add($sortiesOrganisee);
            $sortiesOrganisee->setOrganisateur($this);
        }

        return $this;
    }

    public function removeSortiesOrganisee(Sortie $sortiesOrganisee): self
    {
        if ($this->sortiesOrganisees->removeElement($sortiesOrganisee)) {
            // set the owning side to null (unless already changed)
            if ($sortiesOrganisee->getOrganisateur() === $this) {
                $sortiesOrganisee->setOrganisateur(null);
            }
        }

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): self
    {
        $this->campus = $campus;

        return $this;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImageProfil(): ?Image
    {
        return $this->imageProfil;
    }

    public function setImageProfil(?Image $imageProfil): self
    {
        $this->imageProfil = $imageProfil;

        return $this;
    }


    public function serialize()
    {
        return serialize(array(
            'id' => $this->getId(),
            'mail' => $this->mail,
            'motPasse' => $this->getMotPasse(),
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'telephone' => $this->telephone,
            'administrateur' => $this->administrateur,
            'actif' => $this->actif,
            'pseudo'=> $this->pseudo,
            'campus' => $this->campus,
        ));
    }

    public function unserialize(string $data)
    {
        $donnees = unserialize($data);
        $this->id = $donnees['id'];
        $this->setMail($donnees['mail'])
            ->setMotPasse($donnees['motPasse'])
            ->setNom($donnees['nom'])
            ->setPrenom($donnees['prenom'])
            ->setTelephone($donnees['telephone'])
            ->setAdministrateur($donnees['administrateur'])
            ->setActif($donnees['actif'])
            ->setPseudo($donnees['pseudo'])
            ->setCampus($donnees['campus'])
        ;
    }



}
