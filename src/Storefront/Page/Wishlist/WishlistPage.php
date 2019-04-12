<?php declare(strict_types=1);

namespace Workshop\Plugin\WorkshopWishlist\Storefront\Page\Wishlist;

use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Storefront\Framework\Page\PageWithHeader;
use Workshop\Plugin\WorkshopWishlist\Entity\Wishlist\WishlistEntity;

class WishlistPage extends PageWithHeader
{
    /**
     * @var WishlistEntity
     */
    private $wishlist;

    /**
     * @var bool
     */
    private $customerIsOwner;

    /**
     * @var CmsPageEntity
     */
    protected $cmsPage;

    public function getWishlist(): WishlistEntity
    {
        return $this->wishlist;
    }

    public function setWishlist(WishlistEntity $wishlist): void
    {
        $this->wishlist = $wishlist;
    }

    public function getCmsPage(): CmsPageEntity
    {
        return $this->cmsPage;
    }

    public function setCmsPage(CmsPageEntity $cmsPage): void
    {
        $this->cmsPage = $cmsPage;
    }

    public function isCustomerIsOwner(): bool
    {
        return $this->customerIsOwner;
    }

    public function setCustomerIsOwner(bool $customerIsOwner): void
    {
        $this->customerIsOwner = $customerIsOwner;
    }
}
