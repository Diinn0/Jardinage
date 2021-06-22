<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\OrderLine;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
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

    /**
     * @Route("/article/{slug}/add/{quantity}", name="addToCart")
     */
    public function addProductToCart(String $slug, int $quantity = 1): Response
    {
        $article = $this->entityManager->getRepository(Article::class)->findOneBySlug($slug);

        if(!$article){
            return $this->redirectToRoute('article');
        }

        $session = new Session();
        $session->start();

        $exist = false;
        $cart = $session->get("cart");
        if ($cart == null) {
            $cart = new ArrayCollection();
        } else {
            foreach ($cart as $item) {
                if ($item->getArticle() == $article)
                {
                    $item->setQuantity($item->getQuantity() + $quantity);
                    $exist = true;
                    break;
                }
            }
        }

        if (!$exist) {
            $item = new OrderLine();
            $item->setArticle($article);
            $item->setQuantity($quantity);

            $cart->add($item);
        }
        $session->set("cart", $cart);
        $session->save();

        return $this->redirectToRoute("cart");
    }
}
