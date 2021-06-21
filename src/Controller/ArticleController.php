<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("categories/{id}/article", name="article")
     */
    public function index(int $id): Response
    {
        $category = $this->getDoctrine()
            ->getRepository(Category::class)
            ->find($id);

        $articles = $category->getArticles();
        return $this->render('article/index.html.twig', [
            'articles'=>$articles,
            'category'=>$category
        ]);
    }


    /**
     * @Route("/categories", name="categories")
     */
    public function displayAllCategories(): Response
    {
        $categories = $this->getDoctrine()
            ->getRepository(Category::class)->findAll();

        return $this->render('article/category.html.twig', [
            'categories'=>$categories
        ]);
    }

    /**
     * @Route("/article/{slug}", name="articledetails")
     */
    public function goToProduct($slug): Response
    {
        $article = $this->entityManager->getRepository(Article::class)->findOneBySlug($slug);

        if(!$article){
            return $this->redirectToRoute('article');
        }
        return $this->render('article/articledetails.html.twig', [
            'article'=>$article
        ]);
    }
}
