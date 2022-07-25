<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminUserController extends AbstractController
{
    /**
     * @Route("/admin/user", name="app_admin_user")
     */
    public function index(UserRepository $userRepo, EntityManagerInterface $manager): Response
    {

        return $this->render('admin_user/index.html.twig', [

            'users' => $userRepo->findAll()
        ]);
    }

    /**
     * @Route("/admin/user/editer/{id}", name="app_admin_user_editer")
     */
    public function editerUser(User $user = null, Request $request, EntityManagerInterface $manager): Response
    {
        if(!$user)
        {
            $user = new User;
        }

        $form = $this->createForm(UserFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $manager->flush();

            $this->addFlash("success", "L'utilisateur " .$user->getNom(). " a bien été modifié");

            return $this->redirectToRoute("app_admin_user");
        }


        return $this->render('admin_user/user_editer.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/admin/user/supprimer/{id}", name="app_user_supprimer")
     */
    public function suppUser(User $user, EntityManagerInterface $manager): Response
    {
        $manager->remove($user);
        $manager->flush();

        $this->addFlash('danger', "L'utilisateur " . $user->getNom() . " a bien été supprimé");

        return $this->redirectToRoute("app_admin_user");
    }
}
