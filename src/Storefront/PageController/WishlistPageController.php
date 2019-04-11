<?php declare(strict_types=1);

namespace Workshop\Plugin\WorkshopWishlist\Storefront\PageController;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
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

    public function __construct(EntityRepositoryInterface $wishlistRepository)
    {
        $this->wishlistRepository = $wishlistRepository;
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
        $wishlist = $this->wishlistRepository->search($criteria, $context->getContext())->first();

        $customerId      = $context->getCustomer()->getId();
        $isPublic        = ! $wishlist->isPrivate();
        $customerIsOwner = $customerId === $wishlist->getCustomer()->getId();

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

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('workshop_wishlist.customerId', $context->getCustomer()->getId()));

        $result = $this->wishlistRepository->search($criteria, $context->getContext());

        return $this->renderStorefront('@WorkshopWishlist/page/wishlist/index.html.twig', [
            'wishlists' => $result,
        ]);
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
