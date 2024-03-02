<?php

namespace App\Service;

use App\Entity\CustomerOrder;
use App\Entity\OrderAddress;
use App\Entity\OrderItem;
use App\Repository\AddressRepository;
use App\Repository\CartItemRepository;
use App\Twig\CartExtension;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    private $em;
    private $cartExtension;
    private $addressRepository;
    private $cartItemRepository;

    public function __construct(EntityManagerInterface $em, CartExtension $cartExtension, AddressRepository $addressRepository, CartItemRepository $cartItemRepository)
    {
        $this->em = $em;
        $this->cartExtension = $cartExtension;
        $this->addressRepository = $addressRepository;
        $this->cartItemRepository = $cartItemRepository;
    }

    public function createOrder($user, $selectedAddressId, $selectedPaymentMethod)
    {
        if (empty($selectedAddressId)) {
            throw new \Exception('Address ID cannot be empty.');
        }
    
        $selectedAddress = $this->addressRepository->find($selectedAddressId);
        error_log("Selected Address ID before throwing exception: " . $selectedAddressId);
        if (!$selectedAddress) {
            throw new \Exception('Address not found.');
        }
        $cartItems = $this->cartItemRepository->findBy(['user' => $user]);

        // Create and save the order
        $order = new CustomerOrder();
        $order->setUser($user);
        $order->setOrderStatus('Pending');
        $order->setAddress($selectedAddress);
        $order->setTotalPrice($this->cartExtension->getTotalDiscountedPrice() + $this->cartExtension->getDeliveryCost());
        $order->setTotalQuantity($this->cartExtension->getTotalCartQuantity());
        $order->setPaymentMethod($selectedPaymentMethod);

        // Create and save the order address
        $orderAddress = new OrderAddress();
        $orderAddress->setFirstName($selectedAddress->getFirstName());
        $orderAddress->setLastName($selectedAddress->getLastName());
        $orderAddress->setCity($selectedAddress->getCity());
        $orderAddress->setPostalCode($selectedAddress->getPostalCode());
        $orderAddress->setStreetAddress($selectedAddress->getStreetAddress());
        $orderAddress->setAddressComplement($selectedAddress->getAddressComplement());
        $orderAddress->setPhoneNumber($selectedAddress->getPhoneNumber());
        $orderAddress->setCustomerOrder($order);

        $this->em->persist($orderAddress);
        $order->setOrderAddress($orderAddress);

        // Create and save the order items
        foreach ($cartItems as $cartItem) {
            $orderItem = new OrderItem();
            $orderItem->setProduct($cartItem->getProduct());
            $orderItem->setProductName($cartItem->getProduct()->getproductName());
            $orderItem->setQuantity($cartItem->getQuantity());
            $orderItem->setPrice($cartItem->getProduct()->getproductPrice());
            $orderItem->setDiscountedPrice($cartItem->getProduct()->getPriceAfterDiscount());
            $orderItem->setCustomerOrder($order);

            $this->em->persist($orderItem);
        }

        $this->em->persist($order);
        $this->em->flush();

        return $order;
    }

    public function emptyCart($user)
    {
        $cartItems = $this->cartItemRepository->findBy(['user' => $user]);
        foreach ($cartItems as $cartItem) {
            $this->em->remove($cartItem);
        }
        $this->em->flush();
    }
}
