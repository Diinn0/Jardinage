<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\ForumCategory;
use App\Entity\User;
use App\Entity\UserComment;
use App\Repository\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ForumController extends AbstractController
{
    /**
     * @Route("/forum", name="forum")
     */
    public function index(): Response
    {
        $categoriesRepository = $this->getDoctrine()->getRepository(ForumCategory::class);
        $categories = $categoriesRepository->findMainCategories();
        foreach ($categories as $category) {
            $category->getSubCategories()->initialize();
            $category->getComments()->initialize();
        }
        return $this->render('forum/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/forum/c/{category?}", name="showCategory", priority="2")
     */
    public function showCategory(String $category): Response
    {
        $categoriesRepository = $this->getDoctrine()->getRepository(ForumCategory::class);
        $parentCategory = $categoriesRepository->find($category);
        $categories = $categoriesRepository->findSubCategoriesByMain($parentCategory);
        foreach ($categories as $category) {
            $category->getSubCategories()->initialize();
        }
        $parentCategory->getComments()->initialize();

        return $this->render('forum/index.html.twig', [
            'parentCategory' => $parentCategory,
            'categories' => $categories,
            'parents' => $this->getParents($parentCategory),
        ]);
    }

    /**
     * @Route("/forum/p/{post?}", name="showPost", priority="2")
     */
    public function showPost(String $post): Response
    {
        $commentRepository = $this->getDoctrine()->getRepository(Comment::class);
        /** @var Comment $comment */ $comment = $commentRepository->find($post);

        $comment->getAnswers()->initialize();
        return $this->render('forum/post.html.twig', [
            'comment' => $comment,
            'parents' => $this->getParents($comment->getCategory()),
        ]);
    }

    /**
     * @Route("/forum/p/{post}/send", name="sendPost", methods={"POST"})
     */
    public function sendPost(String $post, Request $request): RedirectResponse
    {
        $commentRepository = $this->getDoctrine()->getRepository(Comment::class);
        $parent = $commentRepository->find($post);
        $userCommentRepository = $this->getDoctrine()->getRepository(UserComment::class);

        $email = $request->get("email");
        $isAnonyme = $request->get("anonyme");
        $content = $request->get("content");

        if ($content == null)
        {
            return $this->redirectToRoute("showPost", [
                'post' => $post,
            ]);
        }

        if ($email == null)
        {
            return $this->redirectToRoute("showPost", [
                'post' => $post,
            ]);
        }

        $user = $userCommentRepository->findOneBy(array("email" => $email));
        if ($user == null) {
            $user = new UserComment();
        }
        $user->setEmail($email);

        $comment = new Comment();

        if ($isAnonyme != null && $isAnonyme == "no") {
            $firstname = $request->get("firstname");
            $lastname = $request->get("lastname");

            if ($firstname != null && $lastname != null)
            {
                $comment->setAnonyme(false);
                $user->setFirstname($firstname);
                $user->setLastname($lastname);
            }
        }

        $user->setIp($request->getClientIp());
        $this->getDoctrine()->getManager()->persist($user);

        $comment->setContent($content);
        $comment->setUserComment($user);
        $comment->setTitle($parent->getTitle());
        $comment->setParents($parent);
        $comment->setDate(new \DateTime());
        $comment->setCategory($parent->getCategory());

        $this->getDoctrine()->getManager()->persist($comment);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute("showPost", [
            'post' => $post,
        ]);
    }

    private function getParents(ForumCategory $category): ArrayCollection
    {
        $categories = new ArrayCollection();
        while ($category->getMainCategory() != null)
        {
            $categories->add($category);
            $category = $category->getMainCategory();
        }
        $categories->add($category);

        return $categories;
    }
}
