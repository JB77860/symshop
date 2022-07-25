<?php

namespace App\Controller;

use DateTime;
use App\Entity\Avis;
use App\Form\AvisFormType;
use App\Repository\AvisRepository;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
    public function detail($id, ProduitRepository $produitRepo, Request $request, EntityManagerInterface $manager): Response
    {
        $produit = $produitRepo->find($id);

        if(!$produit && !is_numeric($id))
        {
            $this->addFlash('warning', "Ce produit n'existe pas");

            return $this->redirectToRoute('app_produits');
        }

        $avis = new Avis;
        $form = $this->createForm(AvisFormType::class, $avis);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $avis->setCreatedAt(new DateTime())
                ->setProduit($produitRepo->find($id));

            $manager->persist($avis);
            $manager->flush();

            $this->addFlash('success', 'Votre avis a bien été posté');

            return $this->redirectToRoute('app_detail_produit', [
                'id' => $id
            ]);
        }

        return $this->render("produit/detail.html.twig", [
            'produit' => $produitRepo->find($id),
            'form' => $form->createView()
        ]);
    }


    /*
       Fonction d'affichage selon la catégorie
     */
    /**
     * @Route("/categories", name="app_categories")
     */
    public function categoriesAll(CategorieRepository $catRepo): Response
    {
        $categories = $catRepo->findAll();

        return $this->render("produit/categories.html.twig", [
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/categorie/{id}", name="app_categorie_produits")
     */
    public function categorieProduit($id, CategorieRepository $catRepo): Response
    {
        $categorie = $catRepo->find($id);

        if(!$categorie)
        {
            $this->addFlash('warning', "Cette catégorie n'existe pas");

            return $this->redirectToRoute('app_categories');
        }

        return $this->render("produit/categorie_produit.html.twig", [
            'categorie' => $categorie
        ]);
    }
}
