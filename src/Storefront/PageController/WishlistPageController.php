<?php declare(strict_types=1);

namespace Workshop\Plugin\WorkshopWishlist\Storefront\PageController;

use Shopware\Core\Checkout\Cart\Exception\CustomerNotLoggedInException;
use Shopware\Core\Framework\Routing\InternalRequest;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WishlistPageController extends StorefrontController
{

    /**
     * @throws CustomerNotLoggedInException
     */
    protected function isUserLoggedIn(): bool 
    {
        /** @var RequestStack $requestStack */
        $requestStack = $this->get('request_stack');
        $request = $requestStack->getMasterRequest();

        if (!$request) {
            return true;
        }

        /** @var SalesChannelContext|null $context */
        $context = $request->attributes->get(PlatformRequest::ATTRIBUTE_STOREFRONT_CONTEXT_OBJECT);

        if ($context && $context->getCustomer() && $context->getCustomer()->getGuest() === false) {
            return true;
        }

        return false;
    }

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

    /**
     * @Route("/wishlist/add/{articleId}", name="frontend.wishlist.add.modal", options={"seo"="false"}, methods={"GET"})
     *
     * @throws CustomerNotLoggedInExceptionAlias
     */
    public function add(string $articleId, InternalRequest $request, SalesChannelContext $context): Response
    {
        if( !$this->isUserLoggedIn() ){
            die("NOT LOGGED IN");
        };

        $lists = [1,2,3,4,5];

            // $this->addressPageLoader->load($request, $context);

        return $this->renderStorefront('@WorkshopWishlist/page/wishlist/add.html.twig', ['lists' => $lists]);
    }

}
