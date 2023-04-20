<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/lieu', name: 'lieu_')]
class LieuController extends AbstractController
{
    #[Route('/', name: 'ajouter')]
    public function ajouter(Request $request, EntityManagerInterface $entityManager): Response
    {
        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class, $lieu);
        $lieuForm->handleRequest($request);
        if ($lieuForm->isSubmitted() && $lieuForm->isValid())
        {
            $entityManager->persist($lieu);
            $entityManager->flush();
            $this->addFlash('success', 'Le lieu ' . $lieu->getNom() . " - " . $lieu->getVille()->getNom() . ' à été ajouté');
            return $this->redirectToRoute('sortie_ajouter');
        }
        return $this->render('lieu/ajouter.html.twig', [
            'controller_name' => 'LieuController',
            'titre'=>'Ajouter un lieu',
            'lieuForm'=>$lieuForm->createView()
        ]);
    }
}
