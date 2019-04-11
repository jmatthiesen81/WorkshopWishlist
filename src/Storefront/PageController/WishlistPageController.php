<?php declare(strict_types=1);

namespace Workshop\Plugin\WorkshopWishlist\Storefront\PageController;

use Shopware\Core\Framework\Routing\InternalRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    /**
     * @Route("/wishlist/modal/{articleId}", name="frontend.wishlist.add.modal", options={"seo"="false"}, methods={"GET"})
     *
     */
    public function modal(string $articleId, InternalRequest $request, SalesChannelContext $context): Response
    {
        $user   = $context->getCustomer();
        $product= ['name' => "ProductName"]; // @TODO: Get Article by ID
        $lists  = [];

        if ( $user ) {
            $lists = [
                ['id' => '13dfns', 'name' => 'Meine Wunschliste', 'articleCount' => 3],
                ['id' => '31vfs2', 'name' => 'Birthday', 'articleCount' => 3],
                ['id' => 'gsdf33', 'name' => 'Wedding', 'articleCount' => 13],
            ]; // @TODO: Get wishlists by $user
        };

        return $this->renderStorefront('@WorkshopWishlist/page/wishlist/modal.html.twig', [
            'loggedIn' => (!empty($user)),
            'lists' => $lists,
            'product' => $product
        ]);
    }

    /**
     * @Route("/wishlist/add/{articleId}/{listId}", name="frontend.wishlist.add.action", options={"seo"="false"}, methods={"GET"})
     *
     */
    public function add(string $articleId, string $listId, InternalRequest $request, SalesChannelContext $context): Response
    {
        $user = $context->getCustomer();
        $data = [];

        try{
            $data['result'] = NULL; // @TODO: Add $articleId to wishlist with ID $listId and userID $user->getId()
        } catch( UserNotLoggedInException $e ){
            $data['error'] = ['code' => 601, 'message' => 'User not logged in'];
        } catch( WishlistNotFound $e ){
            $data['error'] = ['code' => 602, 'message' => 'List not found'];
        }

        return new JsonResponse(
            $data
        );
    }

}
