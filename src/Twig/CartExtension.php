<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\CartItem;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CartExtension extends AbstractExtension
{
    private EntityManagerInterface $entityManager;
    private TokenStorageInterface $tokenStorage;

    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('totalQuantity', [$this, 'getTotalCartQuantity']),
            new TwigFunction('totalDiscountedPrice', [$this, 'getTotalDiscountedPrice']),
            new TwigFunction('deliveryCost', [$this, 'getDeliveryCost']),
            new TwigFunction('totalPrice', [$this, 'getTotalPrice']),
        ];
    }

    public function getTotalCartQuantity(): int
    {
        $user = $this->getUser();

        if (!$user) {
            return 0;
        }

        $cartItemRepository = $this->entityManager->getRepository(CartItem::class);
        $cartItems = $cartItemRepository->findBy(['user' => $user]);

        $totalQuantity = array_sum(array_map(static function ($item) {
            return $item->getQuantity();
        }, $cartItems));

        return $totalQuantity;
    }

    public function getTotalDiscountedPrice(): float
    {
        $user = $this->getUser();

        if (!$user) {
            return 0.0;
        }

        $cartItemRepository = $this->entityManager->getRepository(CartItem::class);
        $cartItems = $cartItemRepository->findBy(['user' => $user]);

        $totalDiscountedPrice = array_sum(array_map(static function ($item) {
            return $item->getProduct()->getPriceAfterDiscount() * $item->getQuantity();
        }, $cartItems));

        return $totalDiscountedPrice;
    }

    public function getDeliveryCost(): float
    {
        $totalDiscountedPrice = $this->getTotalDiscountedPrice();

        if ($totalDiscountedPrice < 30) {
            return 3.0;
        } elseif ($totalDiscountedPrice < 50) {
            return 2.0;
        } elseif ($totalDiscountedPrice < 100) {
            return 1.0;
        }

        return 0.0;
    }

    public function getTotalPrice(): float
    {
        return $this->getTotalDiscountedPrice() + $this->getDeliveryCost();
    }

    private function getUser()
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return null;
        }

        $user = $token->getUser();

        return is_object($user) ? $user : null;
    }
}
