<?php
// src\DataFixtures\AppFixtures.php

namespace App\DataFixtures;

use App\Entity\Users;
use App\Entity\Order;
use App\Entity\AuthUsers;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AppFixtures extends Fixture
{
    private $userPasswordHasher;
    
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {

         // Création d'un user "normal"
         $user = new AuthUsers();
         $user->setEmail("user@bookapi.com");
         $user->setRoles(["ROLE_USER"]);
         $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
         $manager->persist($user);
         
         // Création d'un user admin
         $userAdmin = new AuthUsers();
         $userAdmin->setEmail("admin@bookapi.com");
         $userAdmin->setRoles(["ROLE_ADMIN"]);
         $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "password"));
         $manager->persist($userAdmin);

        $listUsers = [];
        for ($i = 0; $i < 10; $i++) {
            // Création de l'auteur lui-même.
            $author = new Users();
            $author->setNom("Nom " . $i);
            $author->setPrenom("Prenom " . $i);
            $manager->persist($author);
            $listAuthor[] = $author;
        }
        // Création d'une vingtaine de livres ayant pour titre
        for ($i = 0; $i < 20; $i++) {
            $book = new Order();
            $book->setArticles($i);
            $book->setPrix($i);
            $book->setUsers($listAuthor[array_rand($listAuthor)]);

            // On lie le livre à un auteur pris au hasard dans le tableau des auteurs.
             
            // $book->setAuthor($listAuthor[array_rand($listAuthor)]);
            $manager->persist($book);

            $manager->flush();
        }
    }
}