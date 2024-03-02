<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $productName = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $productPrice = null;

    #[ORM\Column(length: 255)]
    private ?string $productImage = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $shortDescription = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $longDescription = null;

    #[ORM\Column(length: 255)]
    private ?string $productBrand = null;

    #[ORM\Column(length: 255)]
    private ?string $productOrigin = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: CartItem::class)]
    private Collection $cartItems;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Category $category = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: OrderItem::class)]
    private Collection $orderItems;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: FavoriteItem::class)]
    private Collection $favoriteItems;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Offer $offer = null;

    public function __construct()
    {
        $this->cartItems = new ArrayCollection();
        $this->orderItems = new ArrayCollection();
        $this->favoriteItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductName(string $productName): static
    {
        $this->productName = $productName;

        return $this;
    }

    public function getProductPrice(): ?string
    {
        return $this->productPrice;
    }

    public function setProductPrice(string $productPrice): static
    {
        $this->productPrice = $productPrice;

        return $this;
    }

    public function getProductImage(): ?string
    {
        return $this->productImage;
    }

    public function setProductImage(string $productImage): static
    {
        $this->productImage = $productImage;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $shortDescription): static
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getLongDescription(): ?string
    {
        return $this->longDescription;
    }

    public function setLongDescription(string $longDescription): static
    {
        $this->longDescription = $longDescription;

        return $this;
    }

    public function getProductBrand(): ?string
    {
        return $this->productBrand;
    }

    public function setProductBrand(string $productBrand): static
    {
        $this->productBrand = $productBrand;

        return $this;
    }

    public function getProductOrigin(): ?string
    {
        return $this->productOrigin;
    }

    public function setProductOrigin(string $productOrigin): static
    {
        $this->productOrigin = $productOrigin;

        return $this;
    }

    /**
     * @return Collection<int, CartItem>
     */
    public function getCartItems(): Collection
    {
        return $this->cartItems;
    }

    public function addCartItem(CartItem $cartItem): static
    {
        if (!$this->cartItems->contains($cartItem)) {
            $this->cartItems->add($cartItem);
            $cartItem->setProduct($this);
        }

        return $this;
    }

    public function removeCartItem(CartItem $cartItem): static
    {
        if ($this->cartItems->removeElement($cartItem)) {
            // set the owning side to null (unless already changed)
            if ($cartItem->getProduct() === $this) {
                $cartItem->setProduct(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setProduct($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getProduct() === $this) {
                $orderItem->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FavoriteItem>
     */
    public function getFavoriteItems(): Collection
    {
        return $this->favoriteItems;
    }

    public function addFavoriteItem(FavoriteItem $favoriteItem): static
    {
        if (!$this->favoriteItems->contains($favoriteItem)) {
            $this->favoriteItems->add($favoriteItem);
            $favoriteItem->setProduct($this);
        }

        return $this;
    }

    public function removeFavoriteItem(FavoriteItem $favoriteItem): static
    {
        if ($this->favoriteItems->removeElement($favoriteItem)) {
            // set the owning side to null (unless already changed)
            if ($favoriteItem->getProduct() === $this) {
                $favoriteItem->setProduct(null);
            }
        }

        return $this;
    }

    public function getOffer(): ?Offer
    {
        return $this->offer;
    }

    public function setOffer(?Offer $offer): static
    {
        $this->offer = $offer;

        return $this;
    }

    public function getPriceAfterDiscount(int $quantity = 1): ?float
    {
        if (!$this->offer) {
            //echo "No offer applied.";
            return $this->productPrice * $quantity;
        }

        $discountValue = $this->offer->getValue();
        //echo "Discount Value: " . $discountValue;

        switch ($this->offer->getType()) {

            case "10% Discount":
                //echo "Applying 10% Discount";
                $finalPrice = $this->productPrice * (1 - $discountValue) * $quantity;
                //echo "Final Price after 10% Discount: " . $finalPrice;
                return $finalPrice;

            case "5% Discount":
                //echo "Applying 5% Discount";
                $finalPrice = $this->productPrice * (1 - $discountValue) * $quantity;
                //echo "Final Price after 5% Discount: " . $finalPrice;
                return $finalPrice;

            case "15% Discount":
                //echo "Applying 15% Discount";
                $finalPrice = $this->productPrice * (1 - $discountValue) * $quantity;
                //echo "Final Price after 15% Discount: " . $finalPrice;
                return $finalPrice;

            default:
                //echo "No specific discount applied. Using default product price.";
                return $this->productPrice * $quantity;
        }
    }

    public function __toString()
    {
        if (is_null($this->productName)) {
            return 'NULL';
        }
        return $this->productName;
    }
}
