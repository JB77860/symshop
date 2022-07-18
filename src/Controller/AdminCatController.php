<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/admin/categorie")
 */
class AdminCatController extends AbstractController
{
    /**
     * @Route("/", name="app_admin_cat_index", methods={"GET"})
     */
    public function index(CategorieRepository $categorieRepository): Response
    {
        return $this->render('admin_cat/index.html.twig', [
            'categories' => $categorieRepository->findAll()
        ]);
    }

    /**
     * @Route("/new", name="app_admin_cat_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $manager, SluggerInterface $slugger): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $categorie->setSlug($slugger->slug($categorie->getNom()));

            $manager->persist($categorie);
            $manager->flush();

            return $this->redirectToRoute('app_admin_cat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin_cat/new.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_admin_cat_show", methods={"GET"})
     */
    public function show(Categorie $categorie): Response
    {
        return $this->render('admin_cat/show.html.twig', [
            'categorie' => $categorie,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_admin_cat_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Categorie $categorie,EntityManagerInterface $manager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           
            $categorie->setSlug($slugger->slug($categorie->getNom()));

            $manager->flush();

            return $this->redirectToRoute('app_admin_cat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin_cat/edit.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_admin_cat_delete", methods={"POST"})
     */
    public function delete(Request $request, Categorie $categorie, CategorieRepository $categorieRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$categorie->getId(), $request->request->get('_token'))) {
            $categorieRepository->remove($categorie, true);
        }

        return $this->redirectToRoute('app_admin_cat_index', [], Response::HTTP_SEE_OTHER);
    }

     /**
     * @Route("/supprimer/{id}", name="app_categorie_supp")
     */
    public function suppCategorie(Categorie $categorie, EntityManagerInterface $manager): Response
    {
        $manager->remove($categorie);
        $manager->flush();

        $this->addFlash('danger', "La catégorie " .$categorie->getNom(). " a bien été supprimé");

        return $this->redirectToRoute("app_admin_cat_index");
    }

}
