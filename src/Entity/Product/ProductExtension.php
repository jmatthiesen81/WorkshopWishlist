<?php declare(strict_types = 1);
namespace Workshop\Plugin\WorkshopWishlist\Entity\Product;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtensionInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Extension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Workshop\Plugin\WorkshopWishlist\Entity\Wishlist\Aggregate\WishlistProduct\WishlistProductDefinition;
use Workshop\Plugin\WorkshopWishlist\Entity\Wishlist\WishlistDefinition;

class ProductExtension implements EntityExtensionInterface
{
    /**
     * Allows to add fields to an entity.
     */
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new ManyToManyAssociationField('wishlists', WishlistDefinition::class, WishlistProductDefinition::class, 'product_id', 'wishlist_id'))->addFlags(new Extension())
        );
    }

    /**
     * Defines which entity definition should be extended by this class
     */
    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }
}
