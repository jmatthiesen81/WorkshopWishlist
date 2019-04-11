<?php declare(strict_types=1);
namespace Workshop\Plugin\WorkshopWishlist\Entity\Wishlist\Aggregate\WishlistProduct;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;
use Workshop\Plugin\WorkshopWishlist\Entity\Wishlist\WishlistDefinition;

class WishlistProductDefinition extends MappingEntityDefinition
{
    public static function getEntityName(): string
    {
        return 'workshop_wishlist_product';
    }

    protected static function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('wishlist_id', 'wishlistId', WishlistDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            new ManyToOneAssociationField('wishlist', 'wishlist_id', WishlistDefinition::class),
            new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class),
        ]);
    }
}