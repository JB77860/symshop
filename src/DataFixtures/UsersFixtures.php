<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class UsersFixtures extends Fixture
{

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for($i = 1; $i <= 10; $i++)
        {
            $user = new User();
            $user->setNom($faker->name())
                ->setPrenom($faker->firstName())
                ->setEmail($faker->email())
                ->setPassword($faker->password())
                ->setRoles(['ROLE_USER'])
                ->setImage($faker->imageUrl(360, 360,'animals', true))
                ->setAdresse($faker->streetAddress())
                ->setCodePostal($faker->postcode())
                ->setVille($faker->city())
                ->setPays($faker->country());

            $manager->persist($user);

        }
       
        $manager->flush();
    }
}
