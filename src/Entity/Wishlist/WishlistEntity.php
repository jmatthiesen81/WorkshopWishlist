<?php declare(strict_types=1);
namespace Workshop\Plugin\WorkshopWishlist\Entity\Wishlist;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class WishlistEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    protected $name;


    /**
     * @var CustomerEntity
     */
    protected $customer;


    /**
     * @var ProductCollection|null
     */
    protected $products;

    /**
     * @var bool
     */
    protected $private;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getProducts(): ?ProductCollection
    {
        return $this->products;
    }

    public function setProducts(ProductCollection $products): void
    {
        $this->products = $products;
    }



    public function getCustomer(): CustomerEntity
    {
        return $this->customer;
    }

    public function setCustomer(CustomerEntity $customer): void
    {
        $this->customer = $customer;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public function getPrivate(): bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): void
    {
        $this->private = $private;
    }

}