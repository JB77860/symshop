<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\InscriptionFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
             return $this->redirectToRoute('app_home');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/inscription", name="app_inscription")
     */
    public function inscription(Request $request, UserPasswordHasherInterface $encoder, EntityManagerInterface $manager): Response
    {
        // On instancie le User qui s'inscrit via le formulaire et on l'insère en BD
        $user = new User;

        //On éxécute la méthode createForm() fournie par la classe AbtractController
        $formInscription = $this->createForm(InscriptionFormType::class, $user);

        // handleRequest() est une méthode du formulaire createForm() qui permet au formulaire de se gorger des infos qui ont été transmises via la request
        // qui contient toutes les super globales de PHP
        $formInscription->handleRequest($request);

        if($formInscription->isSubmitted() && $formInscription->isValid())
        {
            // Si le formulaire a été correctement rempli et les données bien valides( cad si les données saisie ont bien été transmises qux bons setters )
            // Alors on peut faire le traitement
            $passwordHash = $encoder->hashPassword($user, $user->getPassword());

            // On écrase le mdp saisie par le mdp hashé
            $user->setPassword($passwordHash);

            $user->setRoles(["ROLE_USER"]);

            // Save les données en cache
            $manager->persist($user);

            // Insert en BD
            $manager->flush();

            // Fonction addFlash() qui permet d'enregistrer un message en session
            $this->addFlash("success", "Félicitations, votre compte est créé vous pouvez dès à présent vous connecter");

            // Une fois inscrit, on redirige l'utilisateur vers la page de connexion
            return $this->redirectToRoute("app_login");

        }


        return $this->render("security/inscription.html.twig", [
            'form' => $formInscription->createView()
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
