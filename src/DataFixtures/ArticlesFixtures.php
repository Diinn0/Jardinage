<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Provider\Image;

class ArticlesFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $generator = \Faker\Factory::create('fr_FR');
        $categories = ['Graines' => null, 'Plants' => null, 'Outils' => null];
        $tags = ['Graine' => null, 'Légume' => null, 'Fruit' => null, 'Outil' => null, 'Plant' => null, 'Pommier' => null];
        $articles = [
            ['name' => 'Graine de carottes', 'tags' => ['Graine', 'Légume'], 'category' => 'Graines', 'image' => "shirt.jpg"],
            ['name' => 'Graine de radis', 'tags' => ['Graine', 'Légume'], 'category' => 'Graines', 'image' => "shirt.jpg"],
            ['name' => 'Plant de courgette', 'tags' => ['Légume', 'Plant'], 'category' => 'Plants', 'image' => "shirt.jpg"],
            ['name' => 'Plant de melon', 'tags' => ['Fruit', 'Plant'], 'category' => 'Plants', 'image' => "shirt.jpg"],
            ['name' => 'Bêche', 'tags' => ['Outil'], 'category' => 'Outils', 'image' => "shirt.jpg"],
            ['name' => 'Râteau', 'tags' => ['Outil'], 'category' => 'Outils', 'image' => "shirt.jpg"],
            ['name' => 'Plant de pommier : Gala', 'tags' => ['Plant', 'Fruit', 'Pommier'], 'category' => 'Plants', 'image' => "shirt.jpg"],
            ['name' => 'Plant de pommier : Ariane', 'tags' => ['Plant', 'Fruit', 'Pommier'], 'category' => 'Plants', 'image' => "shirt.jpg"]
        ];

        foreach ($categories as $label => $obj) {
            $category = new Category();
            $category->setLabel($label);

            $categories[$label] = $category;
            $manager->persist($category);
        }

        foreach ($tags as $label => $obj) {
            $tag = new Tag();
            $tag->setLabel($label);

            $tags[$label] = $tag;
            $manager->persist($tag);
        }

        foreach ($articles as $object) {
            $article = new Article();
            $article->setLabel($object['name'])
                ->setDescription($generator->text)
                ->setImage($object['image'])
                ->setCategory($categories[$object['category']])
                ->setPrice($generator->randomFloat(2, 2, 100))
                ->setSales($generator->numberBetween(50, 150))
                ->setSlug($object['name']);

            foreach ($object['tags'] as $tag) {
                $article->addTag($tags[$tag]);
            }

            $manager->persist($article);
        }

        $manager->flush();
    }
}
