<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/* On crée un controller pour traiter une partie de l'application, ici la partie produit */
class ProduitController extends AbstractController
{
    /**
     * @Route("/produits", name="app_produits")
     */
    public function index(ProduitRepository $produitRepo): Response
    {

        /* Pour sélectionner des données dans une table SQL, nous avons le Pepository de l'entité correspondante
        Produit => ProduitRepository. Class générée par doctrine qui permet de faire des sélections en BD.
        Pour cela elle dispose de différentes méthodes: find(), findAll(), findByOne() */

        /* Pour pouvoir utiliser le ProduitRepository, on fait de l'injection de dépendance, càd qu'on passe le Repository en argument de la fonction
        l'ArgumentResolver de Symfony se chargera de l'instancier*/

        $produits = $produitRepo->findAll();

        return $this->render('produit/index.html.twig', [
            'produits' => $produits
        ]);
    }

    /**
     * @Route("/produits/{id}", name="app_detail_produit", requirements={"id"="\d+"})
     */
    public function detail($id, ProduitRepository $produitRepo): Response
    {

        return $this->render("produit/detail.html.twig", [
            'produit' => $produitRepo->find($id)
        ]);
    }

}
