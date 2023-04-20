<?php

namespace App\Controller;

use App\Data\VilleFiltre;
use App\Entity\Ville;
use App\Form\FiltreVilleType;
use App\Form\VilleType;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ville', name: 'ville_')]
class VilleController extends AbstractController
{
    #[Route('/', name: 'ajouter')]
    #[Route('/{id}/modifier', name: 'modifier')]
    public function ajouter(Ville $ville=null,EntityManagerInterface $entityManager, Request $request): Response
    {
        // Ajout d'une nouvelle ville
        if ($ville === null)
        {
            $ville = new Ville();
            $message = "La ville à été ajouter";
        } else {
            $message = "La ville à été modifier";
        }


        $villeForm = $this->createForm(VilleType::class, $ville);
        $villeForm->handleRequest($request);


        if ($villeForm->isSubmitted() && $villeForm->isValid())
        {
            $entityManager->persist($ville);
            $entityManager->flush();

            $this->addFlash('success', $message);
            return $this->redirectToRoute('ville_ajouter');
        }

        // Champ de recherche de ville
        $villeFiltre = new VilleFiltre();
        $villeFiltreForm = $this->createForm(FiltreVilleType::class, $villeFiltre);
        $villeFiltreForm->handleRequest($request);
        $villes = $entityManager->getRepository(Ville::class)->findVille($villeFiltreForm->getData());


        return $this->render('ville/ajouter.html.twig', [
            'controller_name' => 'VilleController',
            'villeForm'=>$villeForm->createView(),
            'villeFiltreForm' => $villeFiltreForm->createView(),
            'villes'=>$villes
        ]);
    }


    #[Route('/{id}', name: 'supprimer'), NoReturn]
    public function supprimer(Ville $ville, EntityManagerInterface $entityManager):Response
    {
        $entityManager -> getRepository(Ville::class)->remove($ville);
        $entityManager -> flush();
        $this->addFlash('success', 'La ville à été supprimé');
        return $this->redirectToRoute('ville_ajouter');
    }
}
