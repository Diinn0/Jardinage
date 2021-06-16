<?php

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $generator = \Faker\Factory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setFirstname($generator->firstName)
                ->setLastname($generator->lastName)
                ->setEmail($generator->email)
                ->setPassword($generator->password)
                ->setRoles(['USER']);
            $manager->persist($user);


            $address = new Address();
            $address->setAddress($generator->address)
                ->setCity($generator->city)
                ->setCountry($generator->country)
                ->setZipcode($generator->postcode);

            $manager->persist($address);
            $user->addAddress($address);
        }

        $manager->flush();
    }
}
