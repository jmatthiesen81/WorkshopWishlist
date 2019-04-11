<?php declare(strict_types=1);

namespace Workshop\Plugin\WorkshopWishlist\Storefront\PageController;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Route("/wishlist/{id}", name="frontend.wishlist.item", methods={"GET"})
     *
     * @param SalesChannelContext $context
     * @param string              $id
     *
     * @return Response
     */
    public function item(SalesChannelContext $context, string $id): Response
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

        $result = $this->wishlistRepository->search(new Criteria(), $context->getContext());

        return $this->renderStorefront('@WorkshopWishlist/page/wishlist/index.html.twig', [
            'wishlists' => $result,
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
     * @Route("/wishlist/add/{articleId}", name="frontend.wishlist.add.modal", options={"seo"="false"}, methods={"GET"})
     *
     * @throws CustomerNotLoggedInExceptionAlias
     */
    public function add(string $articleId, SalesChannelContext $context): Response
    {
        $user = $context->getCustomer();

        // Receive Wishlists of User
        if ( $user ) {
            $lists = [1,2,3,4,5];
            // $this->addressPageLoader->load($request, $context);
        };


        return $this->renderStorefront('@WorkshopWishlist/page/wishlist/add.html.twig', [
            'loggedIn' => (!empty($user)),
            'lists' => $lists
        ]);
    }

}
