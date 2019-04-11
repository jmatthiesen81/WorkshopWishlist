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
     * @Route("/wishlist", name="frontend.wishlist.list", methods={"GET"})
     *
     * @param InternalRequest     $request
     * @param SalesChannelContext $context
     *
     * @return Response
     */
    public function index(InternalRequest $request, SalesChannelContext $context): Response
    {
        // if (!$context->getCustomer()) {
        //     return $this->redirectToRoute('frontend.account.login.page');
        // }

        return $this->renderStorefront('@WorkshopWishlist/page/wishlist/index.html.twig');
    }
}
