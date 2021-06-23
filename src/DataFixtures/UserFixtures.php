<?php

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $generator = \Faker\Factory::create('fr_FR');
        $users = new ArrayCollection();

        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setFirstname($generator->firstName)
                ->setLastname($generator->lastName)
                ->setUsername($user->getFirstname().' '.$user->getLastname())
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

            $users->add($user);
        }

        $this->addReference('users', $users);

        $manager->flush();
    }
}
