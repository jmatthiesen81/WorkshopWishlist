<?php declare(strict_types=1);

namespace Workshop\Plugin\WorkshopWishlist\Storefront\Page\Listing;

use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Storefront\Framework\Page\PageWithHeader;

class WishlistPage extends PageWithHeader
{
    /**
     * @var EntitySearchResult
     */
    protected $listing;

    public function getListing(): EntitySearchResult
    {
        return $this->listing;
    }

    public function setListing(EntitySearchResult $listing): void
    {
        $this->listing = $listing;
    }
}
