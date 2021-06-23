<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\OrderLine;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Comment;
use App\Entity\ForumCategory;
use App\Entity\UserComment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
        $forumCategoryRepository = $this->getDoctrine()->getRepository(ForumCategory::class);
        $category = $forumCategoryRepository->findOneBy(array("isArticleMain" => 1));

        $article = $this->entityManager->getRepository(Article::class)->findOneBySlug($slug);

        $commentRepository = $this->getDoctrine()->getRepository(Comment::class);
        $comments = $commentRepository->findByArticle($article);

        foreach ($comments as $comment) {
            $comment->getAnswers()->initialize();
        }

        if(!$article){
            return $this->redirectToRoute('article');
        }
        return $this->render('article/articledetails.html.twig', [
            'article' => $article,
            'category' => $category,
            'comments' => $comments
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

    /**
     * @Route("/article/{slug}/c/", name="newComment", methods={"POST"})
     */
    public function newComment(String $slug, Request $request): RedirectResponse
    {
        $forumCategoryRepository = $this->getDoctrine()->getRepository(ForumCategory::class);
        $category = $forumCategoryRepository->findOneBy(array("isArticleMain" => true));

        $article = $this->entityManager->getRepository(Article::class)->findOneBySlug($slug);

        $userCommentRepository = $this->getDoctrine()->getRepository(UserComment::class);

        $email = $request->get("email");
        $isAnonyme = $request->get("anonyme");
        $content = $request->get("content");
        $title = $request->get("title");

        if ($content == null)
        {
            return $this->redirectToRoute("articledetails", [
                "slug" => $slug
            ]);
        }

        if ($email == null)
        {
            return $this->redirectToRoute("articledetails", [
                "slug" => $slug
            ]);
        }

        $user = $userCommentRepository->findOneBy(array("email" => $email));
        if ($user == null) {
            $user = new UserComment();
        }
        $user->setEmail($email);

        $comment = new Comment();

        if ($isAnonyme != null && $isAnonyme == "no") {
            $username = $request->get("username");

            if ($username != null)
            {
                $comment->setAnonyme(false);
                $user->setUsername($username);
            }
        }

        $user->setIp($request->getClientIp());
        $this->getDoctrine()->getManager()->persist($user);

        $comment->setContent($content);
        $comment->setUserComment($user);
        $comment->setTitle($title);
        $comment->setDate(new \DateTime());
        $comment->setCategory($category);
        $comment->setArticle($article);

        $this->getDoctrine()->getManager()->persist($comment);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute("articledetails", [
            "slug" => $slug
        ]);
    }

    /**
     * @Route("/article/{slug}/c/{commentStr}", name="answerComment", methods={"POST"})
     */
    public function answerComment(String $slug, String $commentStr, Request $request): RedirectResponse
    {
        $commentRepository = $this->getDoctrine()->getRepository(Comment::class);
        $commentParent = $commentRepository->findOneBy(array('id' => $commentStr));

        $article = $this->entityManager->getRepository(Article::class)->findOneBySlug($slug);

        $userCommentRepository = $this->getDoctrine()->getRepository(UserComment::class);

        $email = $request->get("email");
        $isAnonyme = $request->get("anonyme");
        $content = $request->get("content");

        if ($content == null)
        {
            return $this->redirectToRoute("articledetails", [
                "slug" => $slug
            ]);
        }

        if ($email == null)
        {
            return $this->redirectToRoute("articledetails", [
                "slug" => $slug
            ]);
        }

        $user = $userCommentRepository->findOneBy(array("email" => $email));
        if ($user == null) {
            $user = new UserComment();
        }
        $user->setEmail($email);

        $comment = new Comment();

        if ($isAnonyme != null && $isAnonyme == "no") {
            $username = $request->get("username");

            if ($username != null)
            {
                $comment->setAnonyme(false);
                $user->setUsername($username);
            }
        }

        $user->setIp($request->getClientIp());
        $this->getDoctrine()->getManager()->persist($user);

        $comment->setContent($content);
        $comment->setUserComment($user);
        $comment->setTitle($commentParent->getTitle());
        $comment->setDate(new \DateTime());
        $comment->setCategory($commentParent->getCategory());
        $comment->setParents($commentParent);
        $comment->setArticle($article);

        $this->getDoctrine()->getManager()->persist($comment);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute("articledetails", [
            "slug" => $slug
        ]);
    }
}
