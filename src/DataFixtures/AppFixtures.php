<?php

namespace App\DataFixtures;

use App\Entity\CardList;
use App\Entity\Deck;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{

    private const USERS_NAMES = ["Jhon", "Benrnard", "Tony", "Ben"];

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        $decklist = [];
        $userlist = [];

        // --- User ----------------------------------------------
        foreach (self::USERS_NAMES as $UserName) {
            $user = new User();
            $user->setName($UserName);
            $user->setMail($faker->email());
            $user->setPassword($faker->password());
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
                $deck->setImageName($faker->name());
                $decklist[] = $deck;
                $manager->persist($deck);
                $i++;
            }
        }        
        // --- Deck ----------------------------------------------
        foreach ($decklist as $deck) {
            $i = 0;
            while ($i != 3) {
                $card = new CardList();                
                $card->setName($faker->name());
                $card->setAddTo($deck);
                $manager->persist($card);
                $i++;
            }
        }

        $manager->flush();
    }
}
