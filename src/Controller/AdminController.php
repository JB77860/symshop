<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitFormType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="app_admin")
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    /**
     * @Route("/admin/produits", name="app_admin_produits")
     */
    public function adminProduits(ProduitRepository $produitRepo, EntityManagerInterface $manager): Response
    {
        // Grace à manager on peut aller récupérer les champs de la table désirée ici Produit
        $colonnes = $manager->getClassMetadata(Produit::class)->getFieldNames();

        //On récupère tous les produits présents en BD avec le repository
        $produits = $produitRepo->findAll();

        return $this->render("admin/produits.html.twig", [
            'colonnes' => $colonnes,
            'produits' => $produits
        ]);
    }

    /**
     * @Route("/admin/produits/ajouter", name="app_produit_ajouter")
     */
    public function ajouterProduit(Request $request, EntityManagerInterface $manager, SluggerInterface $slugger): Response
    {
        $produit = new Produit;

        $formProduit = $this->createForm(ProduitFormType::class, $produit);

        $formProduit->handleRequest($request);

        if($formProduit->isSubmitted() && $formProduit->isValid())
        {
            /** @var UploadedFile $imageFile */
            $imageFile = $formProduit->get('image')->getData();

            if($imageFile)
            {
                $originalFileName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFileName = $slugger->slug($originalFileName);

                $newFileName = $safeFileName . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try
                {
                    $imageFile->move($this->getParameter('image_directory'), $newFileName);
                } 
                catch(FileException $e)
                {
                    die("Erreur: " . $e->getMessage());
                }

                $produit->setImage($newFileName);
            }

            $manager->persist($produit);

            $manager->flush();

            $this->addFlash('success', "Le produit n° " .$produit->getId()." a bien été ajouté en base de données");

            return $this->redirectToRoute("app_admin_produits");
        }

        return $this->render("admin/produit_ajouter.html.twig", [
            'formProduit' => $formProduit->createView()
        ]);
    }

    /**
     * @Route("/admin/produits/editer/{id}", name="app_produit_editer")
     */
    public function editerProduit(Produit $produit = null, Request $request, EntityManagerInterface $manager, SluggerInterface $slugger): Response
    {

        if(!$produit)
        {
            $produit = new Produit;
        }

        $id = $produit->getId();

        if(!is_numeric($id) || is_null($id))
        {
            $this->addFlash('warning', "Le produit demandé n'existe pas");

            return $this->redirectToRoute('app_admin_produits');
        }

        $form = $this->createForm(ProduitFormType::class, $produit);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if($imageFile)
            {
                $originalFileName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFileName = $slugger->slug($originalFileName);

                $newFileName = $safeFileName . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try
                {
                    $imageFile->move($this->getParameter('image_directory'), $newFileName);
                } 
                catch(FileException $e)
                {
                    die("Erreur: " . $e->getMessage());
                }

                $produit->setImage($newFileName);
            }

            $manager->flush();

            $this->addFlash("info", "Le produit n° $id a bien été modifié");

            return $this->redirectToRoute("app_admin_produits");
        }

        return $this->render("admin/produit_editer.html.twig", [
            'form' => $form->createView(),
            'id' => $id
        ]);
    }

    /**
     * @Route("/admin/produits/supprimer/{id}", name="app_produit_supp")
     */
    public function suppProduit(Produit $produit, EntityManagerInterface $manager): Response
    {
        $manager->remove($produit);
        $manager->flush();

        $this->addFlash('danger', "Le produit " .$produit->getNom(). " a bien été supprimé");

        return $this->redirectToRoute("app_admin_produits");
    }
}
