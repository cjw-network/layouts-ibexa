<?php

declare(strict_types=1);

namespace Netgen\Bundle\EzPublishBlockManagerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

final class NetgenEzPublishBlockManagerExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $locator = new FileLocator(__DIR__ . '/../Resources/config');

        $loader = new DelegatingLoader(
            new LoaderResolver(
                [
                    new GlobFileLoader($container, $locator),
                    new YamlFileLoader($container, $locator),
                ]
            )
        );

        $loader->load('default_settings.yml');
        $loader->load('services/**/*.yml', 'glob');

        $activatedBundles = array_keys($container->getParameter('kernel.bundles'));

        if (!in_array('NetgenLayoutsEnterpriseEzPlatformBundle', $activatedBundles, true)) {
            $loader->load('enterprise/services.yml');
        }

        if (in_array('EzPlatformAdminUiBundle', $activatedBundles, true)) {
            $loader->load('admin/services.yml');
        }

        if (in_array('NetgenTagsBundle', $activatedBundles, true)) {
            $loader->load('eztags/services.yml');
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        $prependConfigs = [
            'block_definitions.yml' => 'netgen_block_manager',
            'query_types.yml' => 'netgen_block_manager',
            'value_types.yml' => 'netgen_block_manager',
            'view/block_view.yml' => 'netgen_block_manager',
            'view/item_view.yml' => 'netgen_block_manager',
            'view/rule_condition_view.yml' => 'netgen_block_manager',
            'view/rule_target_view.yml' => 'netgen_block_manager',
            'ezplatform/image.yml' => 'ezpublish',
        ];

        foreach ($prependConfigs as $configFile => $prependConfig) {
            $configFile = __DIR__ . '/../Resources/config/' . $configFile;
            $config = Yaml::parse((string) file_get_contents($configFile));
            $container->prependExtensionConfig($prependConfig, $config);
            $container->addResource(new FileResource($configFile));
        }
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($this);
    }
}
