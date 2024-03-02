<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\CartItem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/cart')]
class CartController extends AbstractController
{
    #[Route('/', name: 'app_cart')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $cartItemRepository = $entityManager->getRepository(CartItem::class);
        $cartItems = $cartItemRepository->findBy(['user' => $user]);

        return $this->render('cart/index.html.twig', [
            'cartItems' => $cartItems,
        ]);
    }

    #[Route('/add/{productId}', name: 'app_cart_add', methods: ['POST'])]
    public function addToCart(int $productId, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $productRepository = $entityManager->getRepository(Product::class);
        $product = $productRepository->find($productId);

        if (!$product) {
            // Return error response or redirect with error message
            return $this->redirectToRoute('app_cart');
        }

        $quantity = $request->request->getInt('quantity', 1);

        // Check if product already in cart
        $cartItemRepository = $entityManager->getRepository(CartItem::class);
        $existingCartItem = $cartItemRepository->findOneBy(['user' => $user, 'product' => $product]);

        if ($existingCartItem) {
            $existingCartItem->setQuantity($existingCartItem->getQuantity() + $quantity);
        } else {
            $cartItem = new CartItem();
            $cartItem->setUser($user);
            $cartItem->setProduct($product);
            $cartItem->setQuantity($quantity);

            $entityManager->persist($cartItem);
        }

        $entityManager->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true]);
        }

        return $this->redirectToRoute('app_products');
    }

    #[Route('/remove/{productId}', name: 'app_cart_remove', methods: ['POST'])]
    public function removeFromCart(int $productId, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $productRepository = $entityManager->getRepository(Product::class);
        $product = $productRepository->find($productId);

        if (!$product) {
            // Return error response or redirect with error message
            return $this->redirectToRoute('app_cart');
        }

        // Check if product is in cart
        $cartItemRepository = $entityManager->getRepository(CartItem::class);
        $cartItem = $cartItemRepository->findOneBy(['user' => $user, 'product' => $product]);

        if ($cartItem) {
            $entityManager->remove($cartItem);
            $entityManager->flush();
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true]);
        }

        $referrer = $request->headers->get('referer');
        return $referrer ? $this->redirect($referrer) : $this->redirectToRoute('app_products');
    }

    #[Route('/increment/{productId}', name: 'app_cart_increment', methods: ['POST'])]
    public function incrementQuantity(int $productId, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'User not logged in']);
        }

        $product = $entityManager->getRepository(Product::class)->find($productId);

        if (!$product) {
            return new JsonResponse(['success' => false, 'message' => 'Product not found']);
        }

        $cartItem = $entityManager->getRepository(CartItem::class)->findOneBy(['user' => $user, 'product' => $product]);

        if ($cartItem) {
            $cartItem->setQuantity($cartItem->getQuantity() + 1);
            $entityManager->flush();
        }

        return new JsonResponse(['success' => true]);
    }

    #[Route('/decrement/{productId}', name: 'app_cart_decrement', methods: ['POST'])]
    public function decrementQuantity(int $productId, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'User not logged in']);
        }

        $product = $entityManager->getRepository(Product::class)->find($productId);

        if (!$product) {
            return new JsonResponse(['success' => false, 'message' => 'Product not found']);
        }

        $cartItem = $entityManager->getRepository(CartItem::class)->findOneBy(['user' => $user, 'product' => $product]);

        if ($cartItem && $cartItem->getQuantity() > 1) {
            $cartItem->setQuantity($cartItem->getQuantity() - 1);
            $entityManager->flush();
        } else if ($cartItem && $cartItem->getQuantity() == 1) {
            $entityManager->remove($cartItem);
            $entityManager->flush();
        }

        return new JsonResponse(['success' => true]);
    }

    #[Route('/clear', name: 'app_cart_clear', methods: ['POST'])]
    public function clearCart(EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'User not logged in']);
        }

        $cartItems = $entityManager->getRepository(CartItem::class)->findBy(['user' => $user]);

        foreach ($cartItems as $cartItem) {
            $entityManager->remove($cartItem);
        }

        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}
