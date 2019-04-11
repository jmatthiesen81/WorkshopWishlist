<?php declare(strict_types=1);

namespace Workshop\Plugin\WorkshopWishlist\Entity\Wishlist;

use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Workshop\Plugin\WorkshopWishlist\Entity\Wishlist\Aggregate\WishlistProduct\WishlistProductDefinition;

class WishlistDefinition extends EntityDefinition
{
    public static function getEntityName(): string
    {
        return 'workshop_wishlist';
    }

    public static function getCollectionClass(): string
    {
        return WishlistCollection::class;
    }

    public static function getEntityClass(): string
    {
        return WishlistEntity::class;
    }

    protected static function defineFields(): FieldCollection
    {
        return new FieldCollection(
            [
                (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
                (new FkField('customer_id', 'customerId', CustomerDefinition::class))->addFlags(new Required()),
                new ManyToOneAssociationField('customer', 'customer_id', CustomerDefinition::class, 'id', true),
                new StringField('name', 'name'),
                new BoolField('private', 'private'),
                new CreatedAtField(),
                new UpdatedAtField(),
                new ManyToManyAssociationField(
                    'products',
                    ProductDefinition::class,
                    WishlistProductDefinition::class,
                    'wishlist_id',
                    'product_id'),
            ]);
    }
}