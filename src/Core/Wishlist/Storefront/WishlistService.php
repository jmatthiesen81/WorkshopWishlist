<?php declare(strict_types=1);

namespace Workshop\Plugin\WorkshopWishlist\Core\Wishlist\Storefront;

use Shopware\Core\Checkout\Cart\Exception\CartTokenNotFoundException;
use Shopware\Core\Checkout\Cart\Exception\InvalidPayloadException;
use Shopware\Core\Checkout\Cart\Exception\InvalidQuantityException;
use Shopware\Core\Checkout\Cart\Exception\MixedLineItemTypeException;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Storefront\CartService;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Content\Product\Cart\ProductCollector;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Workshop\Plugin\WorkshopWishlist\Entity\Wishlist\WishlistEntity;

class WishlistService
{
    /**
     * @var EntityRepositoryInterface
     */
    private $wishlistRepository;

    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @var EntityRepositoryInterface
     */
    private $productRepository;
    /**
     * @var EntityRepositoryInterface
     */
    private $wishlistProductRepository;

    public function __construct(
        EntityRepositoryInterface $wishlistRepository,
        CartService $cartService,
        EntityRepositoryInterface $productRepository,
        EntityRepositoryInterface $wishlistProductRepository
    ) {
        $this->wishlistRepository = $wishlistRepository;
        $this->productRepository  = $productRepository;
        $this->cartService        = $cartService;
        $this->wishlistProductRepository = $wishlistProductRepository;
    }

    /**
     * @param CustomerEntity $customer
     * @param Context        $context
     *
     * @return EntitySearchResult
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function getWishlistsForUser(CustomerEntity $customer, Context $context)
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('workshop_wishlist.customerId', $customer->getId()));
        $criteria->addAssociation('products');

        return $this->wishlistRepository->search($criteria, $context);
    }

    /**
     * @param array               $listOfIds
     * @param Context             $context
     * @param CustomerEntity|null $customer
     *
     * @return EntitySearchResult
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function getWishlistsByIds(array $listOfIds, Context $context, CustomerEntity $customer = null)
    {
        $criteria = new Criteria($listOfIds);

        if ($customer) {
            $criteria->addFilter(new EqualsFilter('workshop_wishlist.customerId', $customer->getId()));
        }

        $criteria->addAssociation('products');

        return $this->wishlistRepository->search($criteria, $context);
    }

    /**
     * @param string              $listId
     * @param Context             $context
     * @param CustomerEntity|null $customer
     *
     * @return WishlistEntity|null
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function getWishlistById(string $listId, Context $context, CustomerEntity $customer = null)
    {
        return $this->getWishlistsByIds([ $listId ], $context, $customer)->first();
    }

    /**
     * @param string  $productId
     * @param Context $context
     *
     * @return WishlistEntity|null
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function getProductById(string $productId, Context $context)
    {
        return $this->productRepository->search(new Criteria([ $productId ]), $context)->first();
    }

    /**
     * @param array          $wishLists
     * @param Context        $context
     *
     * @return EntityWrittenContainerEvent
     */
    public function createWishlist(array $wishLists, Context $context)
    {
        return $this->wishlistRepository->create($wishLists, $context);
    }

    /**
     * @param string   $productId
     * @param iterable $lists
     * @param Context  $context
     */
    public function addProductToWishlists(string $productId, iterable $lists, Context $context)
    {
        /** @var WishlistEntity $list */
        foreach($lists as $list) {
            $products = [];

            foreach($list->getProducts()->getIds() as $existingProductId) {
                $products[]['id'] = $existingProductId;
            }

            $products[]['id'] = $productId;

            $this->wishlistRepository->update([
                [
                    'id'       => $list->getId(),
                    'products' => $products,
                ]
            ], $context);
        }
    }

    /**
     * @param WishlistEntity $wishlist
     * @param CustomerEntity $customer
     *
     * @return bool
     */
    public function checkAccessToWishlist(WishlistEntity $wishlist, CustomerEntity $customer): bool
    {
        $isPublic        = !$wishlist->isPrivate();
        $customerIsOwner = $customer->getId() === $wishlist->getCustomer()->getId();

        return ($isPublic || $customerIsOwner);
    }

    /**
     * @param string              $token
     * @param WishlistEntity      $wishlist
     * @param SalesChannelContext $context
     *
     * @throws CartTokenNotFoundException
     * @throws InvalidPayloadException
     * @throws InvalidQuantityException
     * @throws MixedLineItemTypeException
     */
    public function addProductsToCart(string $token, WishlistEntity $wishlist, SalesChannelContext $context): void
    {
        foreach ($wishlist->getProducts() as $product) {
            $lineItem = (new LineItem($product->getId(), ProductCollector::LINE_ITEM_TYPE))
                ->setPayload(['id' => $product->getId()])
                ->setRemovable(true)
                ->setStackable(true);

            $this->cartService->add($this->cartService->getCart($token, $context), $lineItem, $context);
        }
    }

    /**
     * @param WishlistEntity $wishlist
     * @param Context $context
     */
    public function removeWishlist(WishlistEntity $wishlist, Context $context): void
    {
        $this->wishlistRepository->delete([['id' => $wishlist->getId()]], $context);
    }

    /**
     * @param WishlistEntity $wishlist
     * @param string $productId
     * @param Context $context
     */
    public function removeProduct(WishlistEntity $wishlist, string $productId, Context $context): void
    {
        $this->wishlistProductRepository
            ->delete([
                [
                'wishlistId' => $wishlist->getId(),
                'productId'=>$productId]]
                , $context);
    }


}
