<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use Faker\Factory;
use App\Entity\Produit;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{

    protected $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {

        // Instanciation de Faker (génération de fausses données réalistes)
        $faker = Factory::create('fr_FR');

        // Ici on crée une boucle pour générer 5 catégories
        for ($j = 0; $j <= 4; $j++) {

            $categorie = new Categorie();

            $categorie->setNom($faker->sentence())
                      ->setSlug($this->slugger->slug($categorie->getNom()));

            $manager->persist($categorie);

            for ($i = 1; $i <= mt_rand(5, 30); $i++) {

                $produit = new Produit();

                $produit->setNom("Produit n° $i")
                    ->setDescription("Voici la description du produit $i")
                    ->setPrix(mt_rand(15, 89))
                    ->setImage("https://picsum.photos/id/" . mt_rand(12, 250) . "/300/160")
                    ->setStock(mt_rand(10, 100))
                    ->setCategorie($categorie);

                // $manager permet de sauvegarder en cache les produits créés
                $manager->persist($produit);
            }
        }

        // Envoie toutes les données créées en BD
        // doctrine:fixtures:load
        $manager->flush();
    }
}
