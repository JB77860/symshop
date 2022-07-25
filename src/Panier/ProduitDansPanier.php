<?php

namespace App\Panier;

use App\Entity\Produit;

class ProduitDansPanier
{
    public $produit;
    public $qte;

    public function __construct(Produit $produit, $qte)
    {
        $this->produit = $produit;
        $this->qte = $qte;
    }

    public function getTotal()
    {
        return $this->produit->getPrix() * $this->qte;
    }
}