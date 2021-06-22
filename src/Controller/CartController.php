<?php

namespace App\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
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
}
