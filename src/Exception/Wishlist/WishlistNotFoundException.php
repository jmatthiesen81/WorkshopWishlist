<?php declare(strict_types=1);

namespace Workshop\Plugin\WorkshopWishlist\Exception\Wishlist;

use Shopware\Core\Framework\ShopwareHttpException;
use Symfony\Component\HttpFoundation\Response;

class WishlistNotFoundException extends ShopwareHttpException
{
    public function __construct(string $wishlistId)
    {
        parent::__construct(
            'Wishlist "{{ wishlistId }}" not found.',
            ['wishlistId' => $wishlistId]
        );
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    public function getErrorCode(): string
    {
        return 'CONTENT__WISHLIST_NOT_FOUND';
    }
}
