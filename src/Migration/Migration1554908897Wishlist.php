<?php declare(strict_types=1);
namespace Workshop\Plugin\WorkshopWishlist\Migration;
use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;
class Migration1554908897Wishlist extends MigrationStep
{
    /**
     * get creation timestamp
     */
    public function getCreationTimestamp(): int
    {
        return 1554908897;
    }

    /**
     * update non-destructive changes
     */
    public function update(Connection $connection): void
    {
        $connection->executeQuery('
            CREATE TABLE IF NOT EXISTS `workshop_wishlist` (
              `id` BINARY(16) NOT NULL,
              `customer_id` BINARY(16) NOT NULL,
              `name` VARCHAR(255) NOT NULL,
            `private` TINYINT(1) NOT NULL DEFAULT \'0\',
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`),
               CONSTRAINT `fk.wishlist_customer.customer_id` FOREIGN KEY (`customer_id`)
                REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeQuery('
            CREATE TABLE IF NOT EXISTS `workshop_wishlist_product` (
              `wishlist_id` BINARY(16) NOT NULL,
              `product_id` BINARY(16) NOT NULL,
              PRIMARY KEY (`wishlist_id`, `product_id`),
              CONSTRAINT `fk.wishlist_product.wishlist_id` FOREIGN KEY (`wishlist_id`)
                REFERENCES `workshop_wishlist` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                   CONSTRAINT `fk.wishlist_product.product_id` FOREIGN KEY (`product_id`) 
                REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }
    /**
     * update destructive changes
     */
    public function updateDestructive(Connection $connection): void
    {
    }
}