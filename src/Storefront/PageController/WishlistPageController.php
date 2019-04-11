<?php declare(strict_types=1);

namespace Workshop\Plugin\WorkshopWishlist\Storefront\PageController;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Routing\InternalRequest;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Workshop\Plugin\WorkshopWishlist\Entity\Wishlist\WishlistEntity;

class WishlistPageController extends StorefrontController
{
    /**
     * @var EntityRepositoryInterface
     */
    private $wishlistRepository;

    /** @var EntityRepositoryInterface */
    private $productRepository;

    public function __construct(EntityRepositoryInterface $wishlistRepository, EntityRepositoryInterface $productRepository)
    {
        $this->wishlistRepository = $wishlistRepository;
        $this->productRepository = $productRepository;
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

        $this->wishlistRepository->create($fakes, $context->getContext());

        return $this->redirectToRoute('frontend.wishlist.index');
    }

    /**
     * @Route("/wishlist/{id}", name="frontend.wishlist.item", methods={"GET"})
     *
     * @param SalesChannelContext $context
     * @param string              $id
     *
     * @return Response
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function item(SalesChannelContext $context, string $id): Response
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('workshop_wishlist.id', $id));

        /** @var WishlistEntity $wishlist */
        $wishlist        = $this->getWishlistById($id, $context->getContext());

        if (!$wishlist) {
            return $this->redirectToRoute('frontend.wishlist.index');
        }

        $customerId      = $context->getCustomer()->getId();
        $isPublic        = ! $wishlist->isPrivate();
        $customerIsOwner = $customerId === $wishlist->getCustomer()->getId();
        $accessDenied    = ! ($isPublic || $customerIsOwner);

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
     * @param SalesChannelContext $context
     *
     * @return Response
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function index(SalesChannelContext $context): Response
    {
        if (!$context->getCustomer()) {
            return $this->redirectToRoute('frontend.account.login.page');
        }

        $result = $lists = $this->getWishlistsForUser($context->getCustomer(), $context->getContext());;

        return $this->renderStorefront('@WorkshopWishlist/page/wishlist/index.html.twig', [
            'wishlists' => $result,
        ]);
    }

    /**
     * @Route("/wishlist/modal/{productId}", name="frontend.wishlist.add.modal", options={"seo"="false"}, methods={"GET"})
     *
     */
    public function modal(string $productId, SalesChannelContext $context): Response
    {
        $user   = $context->getCustomer();
        $product= $this->getProductById($productId, $context->getContext());
        $lists  = [];

        if ( $user ) {
            $lists = $this->getWishlistsForUser($user, $context->getContext());
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
        $post = $request->getPost();

        /** @var array $lists */
        $lists = $post['lists'] ?? NULL;

        /** @var string $listName */
        $listName =$post['listName'] ?? NULL;

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
            $lists[] = $this->createWishlist($listName, $user, $context->getContext()); // @TODO: Create new List with the given name
        }


        // Add Article to List
        $this->addProductToWishlists($productId, $lists, $user, $context->getContext());


        return new JsonResponse(
            ['success' => true]
        );
    }

    private function getWishlistsForUser(CustomerEntity $customer, Context $context){
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('workshop_wishlist.customerId', $customer->getId()));
        $criteria->addAssociation('products');
        return $this->wishlistRepository->search($criteria, $context);
    }

    private function getWishlistById(string $listId, Context $context, CustomerEntity $customer = NULL){
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('workshop_wishlist.id', $listId));
        if($customer) $criteria->addFilter(new EqualsFilter('workshop_wishlist.customerId', $customer->getId()));
        $criteria->addAssociation('products');
        return $this->wishlistRepository->search($criteria, $context)->first();
    }

    private function getProductById(string $productId, Context $context){
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('product.id', $productId));
        return $this->productRepository->search($criteria, $context)->first();
    }

    private function createWishlist(string $listName, CustomerEntity $customer, Context $context){
        $id = Uuid::randomHex();
        $this->wishlistRepository->create([[
            'id' => $id,
            'customerId' => $customer->getId(),
            'name' => $listName,
            'private' => (bool) 1
        ]], $context);
        return $id;
    }

    private function addProductToWishlists(string $productId, array $lists, CustomerEntity $customer, Context $context){

        //$product = $this->getProductById($productId, $context);


        foreach( $lists as $listId){

            /** @var WishlistEntity $list */
            $list = $this->getWishlistById($listId, $context, $customer);

            $products = [];
            foreach( $list->getProducts()->getIds() as $existingProductId){
                $products[]['id'] = $existingProductId;
            }

            $products[]['id'] = $productId;

            $this->wishlistRepository->update([[
                'id' => $list->getId(),
                'products' => $products
            ]], $context);


        }
    }
}
