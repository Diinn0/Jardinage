<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Order;
use App\Entity\OrderLine;
use App\Entity\Article;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Repository\OrderRepository;
use App\Repository\OrderLineRepository;
use App\Repository\ArticleRepository;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setRoles(['USER']);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/account", name="app_account")
     */
    public function account(Request $request, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, UserPasswordEncoderInterface $passwordEncoder) : Response
    {
        $user = $this->getUser();
        if ($user == null){
            return new RedirectResponse($urlGenerator->generate('app_login'));
        }
        //dd($user);

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            //dd($user);

            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('app_account');
        }

        $orders = $entityManager->getRepository(Order::class)->findOrdersByUser($user->getId());
        foreach ($orders as $order) {
            $orderLines = $entityManager->getRepository(OrderLine::class)->findOrderLinesByOrder($order->getId());
            foreach ($orderLines as $orderLine) {
                $article = $entityManager->getRepository(Article::class)->findArticleById($orderLine->getArticle()->getId());
                $orderLine->setArticle($article[0]);
                $order->addOrderLine($orderLine);
            }
        }
        //dd($orders);

        return $this->render('registration/account.html.twig', [
            'orders' => $orders,
            'registrationForm' => $form->createView(),
            'user' => $user,
        ]);
    }
}
