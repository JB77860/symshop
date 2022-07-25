<?php

namespace App\Panier;

use App\Panier\ProduitDansPanier;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PanierService
{

    protected $session;
    protected $produitRepo;

    public function __construct(SessionInterface $session, ProduitRepository $produit)
    {
        $this->session = $session;
        $this->produitRepo = $produit;
    }

    public function ajouter($id)
    {
        // 1- Rerouver le panier sous forme de tableau dans la SESSION
        // Et s'il n'existe pas on le créer

        $panier = $this->session->get('panier', []);

        // Est-ce que le produit via son id existe dans le panier ?
        if(array_key_exists($id, $panier))
        {
            $panier[$id]++;
        }
        else
        {
            $panier[$id] = 1;
        }

        $this->session->set('panier', $panier);
    }

    public function getDetail()
    {
        $panier = [];

        foreach($this->session->get('panier', []) as $id => $qte)
        {
            $produit = $this->produitRepo->find($id);
            if(!$produit)
            {
                continue;
            }

           /*   $total = $qte * $produit->getPrix();
                $panier[] = [
                'produit' => $produit,
                'quantite' => $qte,
                'total' => $total
            ]; */

            $panier[] = new ProduitDansPanier($produit, $qte);
        }

        return $panier;
    }

    // Récupère l'ensemble du total des produits
    public function getTotal()
    {
        $total = 0;

        foreach($this->session->get('panier', []) as $id => $qte)
        {
            $produit = $this->produitRepo->find($id);
            if(!$produit)
            {
                continue;
            }

            $total += ($produit->getPrix() * $qte);
        }

        return $total;

    }

    public function supprimer($id)
    {
        $panier = $this->session->get('panier', []);

        unset($panier[$id]);

        $this->session->set('panier', $panier);
    }


    public function decrementer($id)
    {
        $panier = $this->session->get('panier', []);

        if(!array_key_exists($id, $panier))
        {
            return;
        }
        if($panier[$id] === 1)
        {
            $this->supprimer($id);
            return;
        }
        else
        {
            $panier[$id]--;

            $this->session->set('panier', $panier);
        }
    }

    public function vider()
    {
        return $this->session->set('panier', []);
    }
}