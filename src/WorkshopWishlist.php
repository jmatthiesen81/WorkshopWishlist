<?php declare(strict_types=1);

namespace Workshop\Plugin\WorkshopWishlist;

use Exception;
use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class WorkshopWishlist extends Plugin
{
    /**
     * @param ContainerBuilder $container
     *
     * @throws Exception
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__));
        $loader->load('Storefront/DependencyInjection/services.xml');
    }

    public function getAdministrationEntryPath(): string
    {
        return 'Administration';
    }

    public function uninstall(UninstallContext $context): void
    {
        parent::uninstall($context);

        if ($context->keepUserData()) {
            return;
        }

        $connection = $this->container->get(Connection::class);
        $connection->executeQuery('DROP TABLE IF EXISTS `workshop_wishlist_product`');
        $connection->executeQuery('DROP TABLE IF EXISTS `workshop_wishlist`');
    }
}
