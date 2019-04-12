<?php declare(strict_types=1);

namespace Workshop\Plugin\WorkshopWishlist\Storefront\PageController;

use Shopware\Core\Checkout\Cart\Exception\CartTokenNotFoundException;
use Shopware\Core\Checkout\Cart\Exception\InvalidPayloadException;
use Shopware\Core\Checkout\Cart\Exception\InvalidQuantityException;
use Shopware\Core\Checkout\Cart\Exception\MixedLineItemTypeException;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;
use Shopware\Core\Framework\Routing\InternalRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Controller\StorefrontController;
use Shopware\Storefront\Framework\Page\PageLoaderInterface;
use Shopware\Storefront\Page\Listing\ListingPageLoader;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Workshop\Plugin\WorkshopWishlist\Core\Wishlist\Storefront\WishlistService;
use Workshop\Plugin\WorkshopWishlist\Exception\Wishlist\WishlistNotFoundException;
use Workshop\Plugin\WorkshopWishlist\Storefront\Page\Wishlist\WishlistPageLoader;

class WishlistPageController extends StorefrontController
{
    /**
     * @var WishlistPageLoader|PageLoaderInterface
     */
    private $wishlistPageLoader;

    /**
     * @var ListingPageLoader|PageLoaderInterface
     */
    private $listingPageLoader;

    /**
     * @var WishlistService
     */
    private $wishlistService;

    public function __construct(
        PageLoaderInterface $listingPageLoader,
        PageLoaderInterface $wishlistPageLoader,
        WishlistService $wishlistService
    ) {
        $this->listingPageLoader  = $listingPageLoader;
        $this->wishlistPageLoader = $wishlistPageLoader;
        $this->wishlistService    = $wishlistService;
    }

    /**
     * @Route("/wishlist/fake", name="frontend.wishlist.fake", methods={"GET"})
     *
     * @param SalesChannelContext $context
     *
     * @return Response
     */
    public function fake(SalesChannelContext $context): Response
    {
        $rounds = 10;
        $fakes  = [];

        for (; 0 < $rounds; $rounds--) {
            $fakes[] = [
                'customerId' => $context->getCustomer()->getId(),
                'private'    => (bool) rand(0, 1),
                'name'       => 'Wishlist ' . \md5((string) rand(0, 9999999999)),
            ];
        }

        $this->wishlistService->createWishlist($fakes, $context->getContext());

        return $this->redirectToRoute('frontend.wishlist.index');
    }

    /**
     * @Route(
     *     "/wishlist/{wishlistId}",
     *     name="frontend.wishlist.item",
     *     methods={"GET"}
     * )
     *
     * @param SalesChannelContext $context
     * @param InternalRequest     $request
     *
     * @return Response
     *
     * @throws InconsistentCriteriaIdsException
     * @throws MissingRequestParameterException
     */
    public function item(SalesChannelContext $context, InternalRequest $request): Response
    {
        try {
            $page = $this->wishlistPageLoader->load($request, $context);
        } catch (WishlistNotFoundException $e) {
            return $this->redirectToRoute('frontend.wishlist.index');
        } catch (AccessDeniedException $e) {
            return $this->redirectToRoute('frontend.wishlist.index');
        }

        return $this->renderStorefront('@WorkshopWishlist/page/wishlist/item.html.twig', [
            'page' => $page
        ]);
    }

    /**
     * @Route(
     *     "/wishlist",
     *     name="frontend.wishlist.index",
     *     methods={"GET"}
     * )
     *
     * @param SalesChannelContext $context
     * @param InternalRequest     $request
     *
     * @return Response
     */
    public function index(SalesChannelContext $context, InternalRequest $request): Response
    {
        if (!$context->getCustomer()) {
            return $this->redirectToRoute('frontend.account.login.page');
        }

        $page = $this->listingPageLoader->load($request, $context);

        return $this->renderStorefront('@WorkshopWishlist/page/wishlist/index.html.twig', [
            'page' => $page,
        ]);
    }

    /**
     * @Route(
     *     "/wishlist/modal/{productId}",
     *     name="frontend.wishlist.add.modal",
     *     options={"seo"="false"},
     *     methods={"GET"}
     * )
     *
     * @param string              $productId
     * @param SalesChannelContext $context
     *
     * @return Response
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function modal(string $productId, SalesChannelContext $context): Response
    {
        $user    = $context->getCustomer();
        $product = $this->wishlistService->getProductById($productId, $context->getContext());
        $lists   = [];

        if ( $user ) {
            $lists = $this->wishlistService->getWishlistsForUser($user, $context->getContext());
        };

        return $this->renderStorefront('@WorkshopWishlist/page/wishlist/modal.html.twig', [
            'loggedIn' => (!empty($user)),
            'lists'    => $lists,
            'product'  => $product,
        ]);
    }

    /**
     * @Route(
     *     "/wishlist/add/{productId}",
     *     name="frontend.wishlist.add.action",
     *     options={"seo"="false"}, methods={"POST"}
     * )
     *
     * @param string              $productId
     * @param InternalRequest     $request
     * @param SalesChannelContext $context
     *
     * @return Response
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function add(string $productId, InternalRequest $request, SalesChannelContext $context): Response
    {
        $lists    = [];
        $listIds  = $request->optionalPost('lists');
        $listName = $request->optionalPost('listName', '');
        $user     = $context->getCustomer();

        // Check if User is Logged In
        if (!$user) {
            return new JsonResponse([
                'code'    => 601,
                'message' => 'User not logged in'
            ]);
        }

        // No List or ListName given
        if (! empty($listIds)) {
            $lists = $this->wishlistService->getWishlistsByIds($listIds, $context->getContext(), $user);

            // Add Article to List
            $this->wishlistService->addProductToWishlists($productId, $lists, $context->getContext());
        }

        // Create new List
        if (0 < strlen($listName)) {
            $this->wishlistService->createWishlist([
                [
                    'customerId' => $context->getCustomer()->getId(),
                    'name'       => $listName,
                    'private'    => true,
                    'products'   => [
                        [ 'id' => $productId ]
                    ],
                ]
            ], $context->getContext());
        }

        return new JsonResponse([
            'success' => true
        ]);
    }

    /**
     * @Route(
     *     "/wishlist/addToCart/{wishlistId}",
     *     name="frontend.wishlist.add_to_cart",
     *     options={"seo"="false"},
     *     methods={"POST"}
     * )
     *
     * @param string              $wishlistId
     * @param Request             $request
     * @param SalesChannelContext $context
     *
     * @return RedirectResponse
     *
     * @throws InconsistentCriteriaIdsException
     * @throws WishlistNotFoundException
     * @throws CartTokenNotFoundException
     * @throws InvalidPayloadException
     * @throws InvalidQuantityException
     * @throws MixedLineItemTypeException
     */
    public function addToCart(string $wishlistId,  Request $request, SalesChannelContext $context): RedirectResponse
    {
        $customer = $context->getCustomer();
        if (!$customer) {
            return $this->redirectToRoute('frontend.account.login.page');
        }

        $wishlist = $this->wishlistService->getWishlistById($wishlistId, $context->getContext());
        if (!$wishlist) {
            throw new WishlistNotFoundException($wishlistId);
        }

        if (!$this->wishlistService->checkAccessToWishlist($wishlist, $customer)) {
            return $this->redirectToRoute('frontend.wishlist.index');
        }

        $this->wishlistService->addProductsToCart(
            $request->request->getAlnum('token', $context->getToken()),
            $wishlist,
            $context
        );

        return $this->redirectToRoute('frontend.checkout.cart.page');
    }
    /**
     * @Route("/wishlist/remove/{wishlistId}", name="frontend.wishlist.remove", methods={"POST"})
     *
     * @param string $wishlistId
     * @param Request $request
     * @param SalesChannelContext $context
     * @return RedirectResponse
     * @throws InconsistentCriteriaIdsException
     * @throws WishlistNotFoundException
     */
    public function removeWishlist(string $wishlistId, Request $request, SalesChannelContext $context): RedirectResponse
    {
        $customer = $context->getCustomer();
        if (!$customer) {
            return $this->redirectToRoute('frontend.account.login.page');
        }

        $wishlist = $this->wishlistService->getWishlistById($wishlistId, $context->getContext());
        if (!$wishlist) {
            throw new WishlistNotFoundException($wishlistId);
        }

        if ($this->wishlistService->checkAccessToWishlist($wishlist, $customer, true)) {
            $this->wishlistService->removeWishlist($wishlist,  $context->getContext());
        }

        return $this->redirectToRoute('frontend.wishlist.index');
    }

    /**
     * @Route("/wishlist/{wishlistId}/product/{productId}", name="frontend.wishlist.product.remove", methods={"POST"})
     *
     * @param string $wishlistId
     * @param string $productId
     * @param Request $request
     * @param SalesChannelContext $context
     * @return JsonResponse
     * @throws InconsistentCriteriaIdsException
     * @throws WishlistNotFoundException
     */
    public function removeProductFromWishlist(string $wishlistId, string $productId, Request $request, SalesChannelContext $context): RedirectResponse
    {
        $customer = $context->getCustomer();
        if (!$customer) {
            return $this->redirectToRoute('frontend.account.login.page');
        }

        $wishlist = $this->wishlistService->getWishlistById($wishlistId, $context->getContext());
        if (!$wishlist) {
            throw new WishlistNotFoundException($wishlistId);
        }

        if (!$this->wishlistService->checkAccessToWishlist($wishlist, $customer, true)) {
            return $this->redirectToRoute('frontend.wishlist.index');
        }

        $this->wishlistService->removeProduct($wishlist, $productId,  $context->getContext());

        return $this->redirectToRoute('frontend.wishlist.item', ['wishlistId' => $wishlistId]);
    }


}
