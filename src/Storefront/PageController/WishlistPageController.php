<?php declare(strict_types=1);

namespace Workshop\Plugin\WorkshopWishlist\Storefront\PageController;

use Shopware\Core\Framework\Routing\InternalRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WishlistPageController extends StorefrontController
{
    /**
     * @Route("/wishlist/{id}", name="frontend.wishlist.item", methods={"GET"})
     *
     * @param InternalRequest     $request
     * @param SalesChannelContext $context
     * @param string              $id
     *
     * @return Response
     */
    public function item(InternalRequest $request, SalesChannelContext $context, string $id): Response
    {
        $customerId = $context->getCustomer()->getId();

        // TODO: Check if wishlist is public or the logged in user is the owner of the list
        $accessDenied = true;

        if (!$accessDenied) {
            return $this->redirectToRoute('frontend.account.login.page');
        }

        return $this->renderStorefront('@WorkshopWishlist/page/wishlist/item.html.twig');
    }

    /**
     * @Route("/wishlist", name="frontend.wishlist.list", methods={"GET"})
     *
     * @param InternalRequest     $request
     * @param SalesChannelContext $context
     *
     * @return Response
     */
    public function index(InternalRequest $request, SalesChannelContext $context): Response
    {
        if (!$context->getCustomer()) {
            return $this->redirectToRoute('frontend.account.login.page');
        }

        return $this->renderStorefront('@WorkshopWishlist/page/wishlist/index.html.twig');
    }
}
