<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\ForumCategory;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $generator = \Faker\Factory::create('fr_FR');
        $users = $manager->getRepository(User::class)->findAll();
        $articles = $manager->getRepository(Article::class)->findAll();

        $category = new ForumCategory();
        $category->setTitle('Category');
        $category->setSubtitle($generator->words(4, true));
        $category->setImage('none');
        $manager->persist($category);

        $categoryArticle = new ForumCategory();
        $categoryArticle->setTitle('Article');
        $categoryArticle->setSubtitle($generator->words(4, true));
        $categoryArticle->setArticleMain(true);
        $category->addSubCategory($categoryArticle);
        $categoryArticle->setImage('none');
        $manager->persist($categoryArticle);

        for($i = 0; $i < 5; $i++)
        {
            $comment = new Comment();
            $comment->setContent($generator->text);
            $comment->setTitle($generator->words(2, true));
            $comment->setDate(new \DateTime());
            $comment->setImage("grenouilleContent.svg");
            $comment->setUser($users[array_rand($users, 1)]);
            $comment->setArticle($articles[array_rand($articles, 1)]);
            $manager->persist($comment);

            $comment->setCategory($categoryArticle);
        }

        $commentSingle = new Comment();
        $commentSingle->setContent($generator->text);
        $commentSingle->setTitle($generator->title);
        $commentSingle->setDate(new \DateTime());
        $commentSingle->setImage("grenouilleContent.svg");
        $commentSingle->setUser($users[array_rand($users, 1)]);
        $commentSingle->setArticle(null);
        $manager->persist($commentSingle);
        $commentSingle->setCategory($category);

        $commentSingle->setCategory($categoryArticle);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ArticlesFixtures::class
        ];
    }
}
