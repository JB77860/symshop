<?php

namespace App\Controller;

use DateTime;
use Stripe\Stripe;
use App\Entity\Commande;
use Stripe\PaymentIntent;
use App\Form\PanierFormType;
use App\Panier\PanierService;
use App\Entity\CommandeDetail;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PanierController extends AbstractController
{
    /**
     * @Route("/panier/ajouter/{id}", name="app_panier_ajouter")
     */
    public function ajouter($id, ProduitRepository $produitRepo, PanierService $panierService, Request $request): Response
    {
        /**
         * Array Panier(
         *  indice 0 telProduit [
         *          id =>
         *          nom =>
         *          prix =>
         *          image =>
         *      ]
         * 
         *      1 autreProduit [
         *          id =>
         *          nom =>
         *          prix =>
         *          image =>
         *      ] 
         * )
         */

        /* 1 Est-ce que le produit existe en BD ?
         $produit = $prduitRepo->find($id);
          if(!$produit)
         {
            return $this->redirectToRoute("page_produit") + addFlash("Plus en stock !");

         }

         Si le produit existe on peut l'ajouter

            $panier = $request->getSession()->get('panier', []);

            if(array_key_exists($produit, $panier))
            {
                $panier[$produit]++;
            }
            else
            {
                $panier[$produit]  === 1 place dans le panier;
            }
        */
        $produit = $produitRepo->find($id);

        if (!$produit) {
            $this->addFlash('warning', "Ce produit n'est plus disponible");

            return $this->redirectToRoute("app_produit");
        }

        // On utilise notre fonction ajouter du panierService

        $panierService->ajouter($id);

        $this->addFlash('success', "Le produit a bien été ajouté");

        // On récupère la route où on se trouve pour rediriger en fonction de celle-ci
        $paramRoute = $request->attributes->get('_route');

        if ($paramRoute === 'app_panier') {
            return $this->redirectToRoute("app_panier");
        }
        if ($paramRoute === 'app_produits') {
            return $this->redirectToRoute("app_produits");
        }
        if ($paramRoute === 'app_detail_produit') {
            return $this->redirectToRoute("app_detail_produit", ['id' => $id]);
        } else {

            return $this->redirectToRoute("app_panier");
        }
    }

    /**
     * @Route("/panier", name="app_panier")
     */
    public function voirPanier(PanierService $panierService): Response
    {
        // Pour acc"der au panier ona besoin de la session
        // On a besoin de connaitre les produits en BD et savoir lesquels sont actuellement dans le panier

        /**
         * foreach($session->get('panier', [] as $id => $qte))
         * {
         *      $produit = $produitRepo->find($id);
         * 
         *      if(!produit)
         *      {
         *          continue;
         *      }
         *      else
         *      {
         *          $total = $qte * $produit->getPrix();
         *          $panier[] = [
         *              'produit' => $produit,
         *              'quantite' => $qte,
         *              'total' => $total
         *          ];
         * 
         *          $total += ($produit[prix] * $qte)
         *      }
         * }
         */

        $form = $this->createForm(PanierFormType::class);

        $panier = $panierService->getDetail();
        $total = $panierService->getTotal();




        return $this->render("panier/index.html.twig", [
            'panier' => $panier,
            'total' => $total,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/panier/supprimer/{id}", name="app_panier_supprimer")
     */
    public function supprimer($id, ProduitRepository $produitRepo, PanierService $panierService): Response
    {
        $produit = $produitRepo->find($id);

        if (!$produit) {
            $this->addFlash('warning', "Ce produit n'existe pas");
            return $this->redirectToRoute("app_panier");
        }

        $panierService->supprimer($id);
        $this->addFlash('success', "Le produit a bien été supprimé du panier");

        return $this->redirectToRoute("app_panier");
    }

    /**
     * @Route("/panier/decrementer/{id}", name="app_panier_decrementer")
     */
    public function decrementer($id, PanierService $panierService): Response
    {

        $panierService->decrementer($id);


        return $this->redirectToRoute("app_produits");
    }

    /**
     * @Route("/panier/confirmation", name="app_panier_confirmation")
     */
    public function confirm(Request $request, PanierService $panierService, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(PanierFormType::class);

        $form->handleRequest($request);

        $panierDetail = $panierService->getDetail();

        if (count($panierDetail) === 0) {
            $this->addFlash('warning', "Votre panier est vide");
            return $this->redirectToRoute('app_panier');
        }


        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();

            if (!$user) {
                $this->addFlash('warning', 'Vous devez vous connecter pour valider la commande');
                return $this->redirectToRoute('app_panier');
            }

      
            /**@var Commande */
            $commande = $form->getData();

            $commande->setUser($user)
         ->setDateCommande(new DateTime())
         ->setTotal($panierService->getTotal());

                 
            $manager->persist($commande);

            foreach ($panierService->getDetail() as $unite) {
                $commandeDetail = new CommandeDetail;

                $commandeDetail->setCommande($commande)
                   ->setProduit($unite->produit)
                   ->setNomProduit($unite->produit->getNom())
                   ->setQuantite($unite->qte)
                   ->setTotal($unite->getTotal())
                   ->setPrixProduit($unite->produit->getPrix());

                $manager->persist($commandeDetail);
            }
 
       
            \Stripe\Stripe::setApiKey('sk_test_51LNyAlGBlhi5rmA5LxGrjI05PqadS7AqzSPV33BMC0BxoAdzwuC17oJub2iQjUmbK9yN31aGt6EzvK9clZY2CQrx00ZjTZ1uYE');

                    $checkout_session = \Stripe\Checkout\Session::create([
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price_data' => [
                            'currency' => 'EUR',
                            'product_data' => [
                                'name' => $commandeDetail->getNomProduit()
                            ],
                            'unit_amount' => $commandeDetail->getPrixProduit() * 100
                        ],
                        'quantity' => $commandeDetail->getQuantite()
                    ]],
                    'mode' => 'payment',
                    'success_url' => $this->generateUrl('app_panier_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
                    'cancel_url' => $this->generateUrl('app_panier_defaut', [], UrlGeneratorInterface::ABSOLUTE_URL),
                ]);


            $commande->setStatus('VALIDE');
            $manager->persist($commande);
            $manager->flush();

            $panierService->vider();

            return $this->redirect("$checkout_session->url");

        }
        
        else 
        
        {
            $this->addFlash('warning', "Vous devez remplir le formulaire pour valider votre commande");
            return $this->redirectToroute('app_panier');
        }
    }

    /**
     * @Route("/panier/detail_commande", name="app_panier_detail_commande")
     */
    public function detailCommande(): Response
    {
        $user = $this->getUser();

        $commandes = $user->getCommandes();

        if (!$user) {

            $this->redirectToRoute('app_login');
        }

        return $this->render('panier/detail_commande.html.twig', [
            'commandes' => $commandes
        ]);
    }

     /**
     * @Route("/panier/annulation", name="app_panier_defaut")
     */
    public function annuler(): Response
    {
        return $this->render('panier/confirmation.html.twig');
    }

    /**
     * @Route("/panier/success", name="app_panier_success")
     */
    public function success(): Response
    {
        return $this->render('panier/defaut.html.twig');
    }

}
