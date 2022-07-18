<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminUserController extends AbstractController
{
    /**
     * @Route("/admin/user", name="app_admin_users")
     */
    public function index(UserRepository $userRepository, EntityManagerInterface $manager): Response
    {

        //$colonnes = $manager->getClassMetadata(User::class)->getFieldNames();
        //$users = $userRepository->findAll();

        return $this->render('admin_user/index.html.twig', [

            'users' => $userRepository->findAll(),
            'colonnes' => $manager->getClassMetadata(User::class)->getFieldNames()
        ]);
    }

    /**
     * @Route("/admin/user/show/{id}", name="app_admin_user_show")
     */
    public function showUser(User $user)
    {
        return $this->render('admin_user/show.html.twig', [
            'user' => $user
        ]);
    }

     /**
     * @Route("/admin/user/edit/{id}", name="app_admin_user_edit")
     */
    public function editUser(User $user)
    {
        return $this->render('admin_user/edit.html.twig', [
            'user' => $user
        ]);

    }

    /**
     * @Route("/admin/produits/supprimer/{id}", name="app_user_supp")
     */
    public function suppUser(User $user, EntityManagerInterface $manager): Response
    {
        $manager->remove($user);
        $manager->flush();

        $this->addFlash('danger', "Le membre " .$user->getNom(). " a bien été supprimé");

        return $this->redirectToRoute("app_admin_users");
    }
}
