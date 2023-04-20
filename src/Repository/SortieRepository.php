<?php

namespace App\Repository;

use App\Data\Filtre;
use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function save(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Sortie[] Returns an array of Sortie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * Filtre les différentes sorties selon la recherche sur la page d'accueil
     * @return sortie[]
     */
  


    public function findOuverteACloturee(): array {
        $now = new \DateTime();
        return $this->createQueryBuilder('s')
            ->innerJoin('s.etat', 'e')
            ->andWhere('e.libelle = :libelle')
            ->andWhere('s.dateLimiteInscription < :date')
            ->setParameter('libelle', Etat::$OUVERTE)
            ->setParameter('date', $now)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findClotureeAEnCours(): array {
        $now = new \DateTime();
        return $this->createQueryBuilder('s')
            ->innerJoin('s.etat', 'e')
            ->andWhere('e.libelle = :libelle')
            ->andWhere('s.dateHeureDebut < :date')
            ->setParameter('libelle', Etat::$CLOTUREE)
            ->setParameter('date', $now)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findEnCoursAPassee(): array {
        $now = new \DateTime();
        return $this->createQueryBuilder('s')
            ->innerJoin('s.etat', 'e')
            ->andWhere('e.libelle = :libelle')
            ->andWhere(':date > DATE_ADD(s.dateHeureDebut, s.duree, \'MINUTE\')')
            ->setParameter('libelle', Etat::$EN_COURS)
            ->setParameter('date', $now)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findPasseeOuAnnuleeAArchivee(): array {
        $now = new \DateTime();
        return $this->createQueryBuilder('s')
            ->innerJoin('s.etat', 'e')
            ->andWhere('e.libelle = :premierLibelle OR e.libelle = :secondLibelle')
            ->andWhere(':date > s.dateHeureDebut + :tempsAvantArchivage')
            ->setParameter('premierLibelle', Etat::$PASSEE)
            ->setParameter('secondLibelle', Etat::$ANNULEE)
            ->setParameter('date', $now)
            ->setParameter('tempsAvantArchivage', \DateInterval::createFromDateString('6 month'))
            ->getQuery()
            ->getResult()
        ;
    }

    public function findWithParameters(
        ?string $nom,
        ?Campus $campus,
        ?\DateTime $dateDebutRecherche,
        ?\DateTime $dateFinRecherche,
        ?bool $organisateur,
        ?bool $inscrit,
        ?bool $pasInscrit,
        ?bool $sortiesPassees,
        Participant $moi
    ): array {
        $query = $this->createQueryBuilder('s')
            ->innerJoin('s.etat', 'e');


        foreach(explode(' ', $nom) as $mot) {
            $query->andWhere('s.nom LIKE :motif')
                ->setParameter('motif', '%'.$mot.'%');
        }

        if ($campus != null) {
            $query->andWhere('s.campus = :campus')
                ->setParameter('campus', $campus);
        }
        if ($dateDebutRecherche != null) {
            $query->andWhere('s.dateHeureDebut > :dateDebutRecherche')
                ->setParameter('dateDebutRecherche', $dateDebutRecherche);
        }
        if ($dateFinRecherche != null) {
            $query->andWhere('s.dateHeureDebut < :dateFinRecherche')
                ->setParameter('dateFinRecherche', $dateFinRecherche);
        }
        if ($organisateur == true) {
            $query->andWhere('s.organisateur = :moi')
                ->setParameter('moi', $moi);
        }
        if ($inscrit == true) {
            $query->andWhere(':moi MEMBER OF s.participants')
                ->setParameter('moi', $moi);
        }
        if ($pasInscrit == true) {
            $query->andWhere(':moi NOT MEMBER OF s.participants')
                ->setParameter('moi', $moi);
        }
        if ($sortiesPassees == true) {
            $query->andWhere('e.libelle = :libelle')
                ->setParameter('libelle', Etat::$PASSEE);
        }

        return $query->getQuery()->getResult();
    }

    public function findListe(Participant $utilisateur):array
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.etat','e')
            ->innerJoin('s.organisateur', 'o')
            ->andWhere('e.libelle NOT LIKE \'Archivée\'')
            ->andWhere('e.libelle NOT LIKE \'Créée\' OR o.mail = :mail')
            ->setParameter('mail', $utilisateur->getMail())
            ->getQuery() ->getResult();
    }

}
