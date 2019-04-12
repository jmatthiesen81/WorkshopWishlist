<?php declare(strict_types=1);

namespace Workshop\Plugin\WorkshopWishlist\Storefront\Page\Listing;

use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Routing\InternalRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Page\PageLoaderInterface;
use Shopware\Storefront\Framework\Page\PageWithHeaderLoader;
use Shopware\Storefront\Framework\Page\StorefrontSearchResult;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Workshop\Plugin\WorkshopWishlist\Core\Wishlist\Storefront\WishlistService;

class WishlistPageLoader implements PageLoaderInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var PageWithHeaderLoader|PageLoaderInterface
     */
    private $pageWithHeaderLoader;

    /**
     * @var WishlistService
     */
    private $wishlistService;

    public function __construct(
        PageLoaderInterface $pageWithHeaderLoader,
        EventDispatcherInterface $eventDispatcher,
        WishlistService $wishlistService
    ) {
        $this->eventDispatcher      = $eventDispatcher;
        $this->pageWithHeaderLoader = $pageWithHeaderLoader;
        $this->wishlistService      = $wishlistService;

    }

    /**
     * @param InternalRequest     $request
     * @param SalesChannelContext $context
     *
     * @return WishlistPage
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function load(InternalRequest $request, SalesChannelContext $context): WishlistPage
    {
        $page = $this->pageWithHeaderLoader->load($request, $context);
        $page = WishlistPage::createFrom($page);

        $listing = $this->loadWishlists($context);

        $page->setListing($listing);

        $this->eventDispatcher->dispatch(
            WishlistPageLoadedEvent::NAME,
            new WishlistPageLoadedEvent($page, $context, $request)
        );

        return $page;
    }

    /**
     * @param SalesChannelContext $context
     *
     * @return StorefrontSearchResult
     *
     * @throws InconsistentCriteriaIdsException
     */
    private function loadWishlists(SalesChannelContext $context): StorefrontSearchResult
    {
        $entitySearchResult = $this->wishlistService->getWishlistsForUser($context->getCustomer(), $context->getContext());

        return StorefrontSearchResult::createFrom($entitySearchResult);

    }
}
