<?php

namespace App\DataFixtures;

use App\Entity\Card;
use App\Entity\Deck;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private const USERS_NAMES = ["Jhon", "Benrnard", "Tony", "Ben"];
    private const CARD_NAME = ["Agate Instigator", "Parting Gust", "Starfall Invocation"];

    public function __construct(
        private UserPasswordHasherInterface $hasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        $decklist = [];
        $userlist = [];


        // --- ADMIN le seul qui a des vrai carte ------------------------------------------------
        $admin = new User();
        $admin
            ->setMail("admin@test.flop")
            ->setName('Adminman')
            ->setRoles(["ROLE_ADMIN"])        
            ->setPassword($this->hasher->hashPassword($admin, "admin1234"));

        $manager->persist($admin);

        // --- ADMIN Un deck qui existe vraiment -----------------------------------------------
        $deckAdmin = new Deck();
        $deckAdmin->setName('Boros agro');
        $deckAdmin->setCommanderName('Phlage, Titan of Fire\'s Fury');
        $deckAdmin->setCreator($admin);
        $manager->persist($deckAdmin);

        // --- ADMIN Une list de carte qui existe vraiment ------------------------------------------------
        foreach (self::CARD_NAME as $cardAdminName) {
            $cardAdmin = new Card();                
            $cardAdmin->setName($cardAdminName);
            $cardAdmin->setAddTo($deckAdmin);
            $manager->persist($cardAdmin);
        }

        // --- User ----------------------------------------------
        foreach (self::USERS_NAMES as $UserName) {
            $user = new User();
            $user->setName($UserName)
                ->setMail($faker->email())
                ->setPassword($this->hasher->hashPassword($user, $faker->password()));
            $userlist[] = $user;
            $manager->persist($user);
        }
        // --- Deck ----------------------------------------------
        foreach ($userlist as $user) {
            $i = 0;
            while ($i != 3) {
                $deck = new Deck();
                $deck->setName($faker->name());
                $deck->setCommanderName($faker->name());
                $deck->setCreator($user);
                $decklist[] = $deck;
                $manager->persist($deck);
                $i++;
            }
        }        
        // --- Deck ----------------------------------------------
        foreach ($decklist as $deck) {
            $i = 0;
            while ($i != 3) {
                $card = new Card();                
                $card->setName($faker->name());
                $card->setAddTo($deck);
                $manager->persist($card);
                $i++;
            }
        }

        $manager->flush();
    }
}
