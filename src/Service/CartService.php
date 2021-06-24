<?php

namespace App\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Session\Session;

class CartService
{
    public function getCart()
    {
        $session = new Session();
        $cart = $session->get('cart');

        if ($cart == null) {
            $cart = new ArrayCollection();
        }

        return $cart;
    }

    public function getItemCount()
    {
        $cart = $this->getCart();

        $total = 0;
        foreach ($cart as $item) {
            $total += $item->getQuantity();
        }

        return $total;
    }
}