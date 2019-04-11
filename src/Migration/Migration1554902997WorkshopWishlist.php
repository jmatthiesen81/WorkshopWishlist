<?php


namespace Workshop\Plugin\WorkshopWishlist\Migration;


use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1554902997WorkshopWishlist extends MigrationStep
{

    public function getCreationTimestamp(): int
    {
        return 1554902997;
    }

    /**
     * update non-destructive changes
     */
    public function update(Connection $connection): void
    {
//        $connection->executeQuery('');
    }

    /**
     * update destructive changes
     */
    public function updateDestructive(Connection $connection): void
    {
    }


}
