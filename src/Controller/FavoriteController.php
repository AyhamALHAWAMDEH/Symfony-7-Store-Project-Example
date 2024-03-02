<?php

namespace App\Controller;

use App\Entity\FavoriteItem;
use App\Entity\Product;
use App\Entity\CartItem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class FavoriteController extends AbstractController
{
    #[Route('/favorite', name: 'app_favorite')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $favoriteItems = $entityManager->getRepository(FavoriteItem::class)->findBy(['user' => $user]);
        $cartItems = $entityManager->getRepository(CartItem::class)->findBy(['user' => $user]);

        $productsInCart = array_map(fn($cartItem) => $cartItem->getProduct()->getId(), $cartItems);

        return $this->render('favorite/index.html.twig', [
            'favoriteItems' => $favoriteItems,
            'productsInCart' => $productsInCart,
        ]);
    }

    #[Route('/favorite/add/{productId}', name: 'app_favorite_add', methods: ['POST'])]
    public function addToFavorite(int $productId, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $product = $entityManager->getRepository(Product::class)->find($productId);
        if (!$product) {
            return $this->redirectToRoute('app_favorite');
        }

        $existingFavoriteItem = $entityManager->getRepository(FavoriteItem::class)->findOneBy(['user' => $user, 'product' => $product]);
        if ($existingFavoriteItem) {
            $this->addFlash('warning', 'This product is already in your favorites!');
            return $this->redirectToRoute('app_favorite');
        } else {
            $favoriteItem = new FavoriteItem();
            $favoriteItem->setUser($user);
            $favoriteItem->setProduct($product);

            $entityManager->persist($favoriteItem);
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => true]);
            }

            return $this->redirectToRoute('app_products');
        }
    }

    #[Route('/favorite/remove/{productId}', name: 'app_favorite_remove', methods: ['POST'])]
    public function removeFromFavorite(int $productId, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $product = $entityManager->getRepository(Product::class)->find($productId);
        if (!$product) {
            return $this->redirectToRoute('app_favorite');
        }

        $favoriteItem = $entityManager->getRepository(FavoriteItem::class)->findOneBy(['user' => $user, 'product' => $product]);
        if ($favoriteItem) {
            $entityManager->remove($favoriteItem);
            $entityManager->flush();
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true]);
        }

        return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('app_products'));
    }
}

