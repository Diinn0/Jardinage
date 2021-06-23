<?php

namespace App\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Order;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class CartController extends AbstractController
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @Route("/cart", name="cart")
     */
    public function index(): Response
    {
        return $this->render('cart/index.html.twig');
    }

    /**
     * @Route("/cart/removeOne/{id}", name="removeOne")
     */
    public function removeOne($id): Response
    {
        $session = new Session();
        $cart = $session->get("cart");

        if ($cart == null) {
            $this->redirectToRoute('cart');
        }

        $item = $cart->get($id);
        if ($item != null)
        {
            $qty = $item->getQuantity() - 1;
            if ($qty != 0) {
                $item->setQuantity($qty);
            } else {
                $cart->remove($id);
            }
        }

        return $this->redirectToRoute('cart');
    }

    /**
     * @Route("/cart/addOne/{id}", name="addOne")
     */
    public function addOne($id): Response
    {
        $session = new Session();
        $cart = $session->get("cart");

        if ($cart == null) {
            $this->redirectToRoute('cart');
        }

        $item = $cart->get($id);
        if ($item != null)
        {
            $qty = $item->getQuantity() + 1;
            $item->setQuantity($qty);
        }

        return $this->redirectToRoute('cart');
    }

    /**
     * @Route("/cart/remove/{id}", name="remove")
     */
    public function remove($id): Response
    {
        $session = new Session();
        $cart = $session->get("cart");

        if ($cart == null) {
            $this->redirectToRoute('cart');
        }

        $item = $cart->get($id);
        if ($item != null)
        {
            $cart->remove($id);
        }

        return $this->redirectToRoute('cart');
    }

    /**
     * @Route("/cart/pay", name="pay")
     */
    public function createCommand(): Response
    {
        $session = new Session();
        $cart = $session->get('cart');

        if ($cart == null)
        {
            return $this->redirectToRoute('cart');
        }

        /**
         * @var User $user
         */
        $user = $this->security->getUser();
        if ($user != null) {
            $order = new Order();
            $total = 0;
            foreach ($cart as $item) {
                $order->addOrderLine($item);
                $total += $item->getQuantity() * $item->getArticle()->getPrice();

                $this->getDoctrine()->getManager()->persist($item);
            }

            $order->setSum($total);
            $order->setDate(new \DateTime());
            $order->setUser($user);

            $this->getDoctrine()->getManager()->persist($order);
            $this->getDoctrine()->getManager()->flush();
            $cart->clear();
            $session->save();
        }

        return $this->redirectToRoute('cart');
    }
}
