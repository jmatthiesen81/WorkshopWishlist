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
        $wishlist = [];

        foreach ($this->getFakeData($context->getCustomer()->getId()) as $entry) {
            if ($entry['id'] !== $id) {
                continue;
            }

            $wishlist = $entry;
        }

        $customerId      = $context->getCustomer()->getId();
        $isPublic        = (bool) $wishlist['public'];
        $customerIsOwner = $customerId === $wishlist['customer_id'];

        // TODO: Check if wishlist is public or the logged in user is the owner of the list
        $accessDenied = ! ($isPublic || $customerIsOwner);

        if ($accessDenied) {
            return $this->redirectToRoute('frontend.wishlist.index');
        }

        return $this->renderStorefront('@WorkshopWishlist/page/wishlist/item.html.twig', [
            'wishlist'        => $wishlist,
            'customerIsOwner' => $customerIsOwner,
        ]);
    }

    /**
     * @Route("/wishlist", name="frontend.wishlist.index", methods={"GET"})
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

        $fakeData = $this->getFakeData($context->getCustomer()->getId());

        return $this->renderStorefront('@WorkshopWishlist/page/wishlist/index.html.twig', [
            'wishlists' => $fakeData,
        ]);
    }

    /**
     * @param string $customerId
     *
     * @return array
     */
    private function getFakeData(string $customerId): array
    {
        return [
            [
                'id'          => 'test_1',
                'name'        => 'Test 1',
                'customer_id' => md5((string) \rand(0, 999999)),
                'public'      => 1,
            ],
            [
                'id'          => 'test_2',
                'name'        => 'Test 2',
                'customer_id' => md5((string) \rand(0, 999999)),
                'public'      => 0,
            ],
            [
                'id'          => 'test_3',
                'name'        => 'Test 3',
                'customer_id' => md5((string) \rand(0, 999999)),
                'public'      => 1,
            ],
            [
                'id'          => 'test_4',
                'name'        => 'Test 4',
                'customer_id' => md5((string) \rand(0, 999999)),
                'public'      => 0,
            ],
            [
                'id'          => 'test_5',
                'name'        => 'Test 5',
                'customer_id' => $customerId,
                'public'      => 0,
            ],
        ];
    }

    /**
     * @Route("/wishlist/modal/{productId}", name="frontend.wishlist.add.modal", options={"seo"="false"}, methods={"GET"})
     *
     */
    public function modal(string $productId, InternalRequest $request, SalesChannelContext $context): Response
    {
        $user   = $context->getCustomer();
        $product= ['id' => '1234', 'name' => 'ProductName']; // @TODO: Get Product by $productId
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
     * @Route("/wishlist/add/{productId}", name="frontend.wishlist.add.action", options={"seo"="false"}, methods={"POST"})
     *
     */
    public function add(string $productId, InternalRequest $request, SalesChannelContext $context): Response
    {
        $lists = $request->getParam('lists', []);
        $listName = $request->getParam('listName', NULL);
        $user = $context->getCustomer();
        $data = [];

        // Check if User is Logged In
        if( !$user ){
            return new JsonResponse(['code' => 601, 'message' => 'User not logged in']);
        }

        // No List or ListName given
        if(!$listName && empty($lists)){
            return new JsonResponse(['code' => 603, 'message' => 'No List given']);
        }

        // Create new List
        if($listName){
            $lists[] = 123; // @TODO: Create new List with the given name
        }



        // Add Article to List
        try{
            $data['result'] = true; // @TODO: Add $articleId to wishlist with IDs $lists and userID $user->getId() ($lists = Array)
        } catch( WishlistNotFound $e ){
            $data['error'] = ['code' => 602, 'message' => 'List not found'];
        }

        return new JsonResponse(
            $data
        );
    }

}
