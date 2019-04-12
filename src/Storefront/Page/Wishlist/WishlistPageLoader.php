<?php declare(strict_types=1);

namespace Workshop\Plugin\WorkshopWishlist\Storefront\Page\Wishlist;

use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Content\Cms\SlotDataResolver\ResolverContext\EntityResolverContext;
use Shopware\Core\Content\Cms\SlotDataResolver\SlotDataResolver;
use Shopware\Core\Content\Cms\Storefront\StorefrontCmsPageRepository;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;
use Shopware\Core\Framework\Routing\InternalRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Page\PageLoaderInterface;
use Shopware\Storefront\Framework\Page\PageWithHeaderLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Workshop\Plugin\WorkshopWishlist\Core\Wishlist\Storefront\WishlistService;
use Workshop\Plugin\WorkshopWishlist\Entity\Wishlist\WishlistEntity;
use Workshop\Plugin\WorkshopWishlist\Exception\Wishlist\WishlistNotFoundException;

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
     * @var StorefrontCmsPageRepository
     */
    private $cmsPageRepository;

    /**
     * @var SlotDataResolver
     */
    private $slotDataResolver;

    /**
     * @var WishlistService
     */
    private $wishlistService;

    public function __construct(
        PageLoaderInterface $pageWithHeaderLoader,
        EventDispatcherInterface $eventDispatcher,
        StorefrontCmsPageRepository $cmsPageRepository,
        SlotDataResolver $slotDataResolver,
        WishlistService $wishlistService
    ) {
        $this->eventDispatcher      = $eventDispatcher;
        $this->pageWithHeaderLoader = $pageWithHeaderLoader;
        $this->cmsPageRepository    = $cmsPageRepository;
        $this->slotDataResolver     = $slotDataResolver;
        $this->wishlistService      = $wishlistService;

    }

    /**
     * @param InternalRequest     $request
     * @param SalesChannelContext $context
     *
     * @return WishlistPage
     *
     * @throws InconsistentCriteriaIdsException
     * @throws WishlistNotFoundException
     * @throws MissingRequestParameterException
     */
    public function load(InternalRequest $request, SalesChannelContext $context): WishlistPage
    {
        $page = $this->pageWithHeaderLoader->load($request, $context);
        $page = WishlistPage::createFrom($page);

        $wishlistId = $request->requireGet('wishlistId');
        $wishlist   = $this->loadWishlist($wishlistId, $context);

        $page->setWishlist($wishlist);

        $customerId      = $context->getCustomer()->getId();
        $isPublic        = ! $wishlist->isPrivate();

        $page->setCustomerIsOwner($customerId === $wishlist->getCustomer()->getId());

        if (! ($isPublic || $page->isCustomerIsOwner())) {
            throw new AccessDeniedException($wishlistId);
        }


        if ($cmsPage = $this->getCmsPage($context)) {
            $this->loadSlotData($cmsPage, $context, $wishlist);
            $page->setCmsPage($cmsPage);
        }

        $this->eventDispatcher->dispatch(
            WishlistPageLoadedEvent::NAME,
            new WishlistPageLoadedEvent($page, $context, $request)
        );

        return $page;
    }

    private function loadSlotData(CmsPageEntity $page, SalesChannelContext $context, WishlistEntity $wishlist): void
    {
        if (!$page->getBlocks()) {
            return;
        }

        $resolverContext = new EntityResolverContext($context, ProductDefinition::class, $wishlist);
        $slots = $this->slotDataResolver->resolve($page->getBlocks()->getSlots(), $resolverContext);

        $page->getBlocks()->setSlots($slots);
    }

    private function getCmsPage(SalesChannelContext $context): ?CmsPageEntity
    {
        $pages = $this->cmsPageRepository->getPagesByType('wishlist_detail', $context);

        if ($pages->count() === 0) {
            return null;
        }

        /** @var CmsPageEntity $page */
        $page = $pages->first();

        return $page;
    }

    /**
     * @param string              $wishlistId
     * @param SalesChannelContext $context
     *
     * @return WishlistEntity
     * @throws WishlistNotFoundException
     *
     * @throws InconsistentCriteriaIdsException
     */
    private function loadWishlist(string $wishlistId, SalesChannelContext $context): WishlistEntity
    {
        $wishlist = $this->wishlistService->getWishlistById($wishlistId, $context->getContext());

        if (!$wishlist) {
            throw new WishlistNotFoundException($wishlistId);
        }

        return $wishlist;
    }
}
