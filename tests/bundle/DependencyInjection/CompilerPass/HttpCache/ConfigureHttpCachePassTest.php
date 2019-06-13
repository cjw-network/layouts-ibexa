<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsEzPlatformBundle\Tests\DependencyInjection\CompilerPass\HttpCache;

use EzSystems\PlatformHttpCacheBundle\PurgeClient\LocalPurgeClient;
use EzSystems\PlatformHttpCacheBundle\PurgeClient\VarnishPurgeClient;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\ContainerBuilderHasAliasConstraint;
use Netgen\Bundle\LayoutsEzPlatformBundle\DependencyInjection\CompilerPass\HttpCache\ConfigureHttpCachePass;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;

final class ConfigureHttpCachePassTest extends AbstractCompilerPassTestCase
{
    /**
     * @covers \Netgen\Bundle\LayoutsEzPlatformBundle\DependencyInjection\CompilerPass\HttpCache\ConfigureHttpCachePass::process
     * @dataProvider processProvider
     */
    public function testProcess(string $definitionClass, bool $clientEnabled): void
    {
        $this->setDefinition('netgen_layouts.http_cache.client', new Definition());
        $this->setDefinition('ezplatform.http_cache.purge_client_internal', new Definition($definitionClass));

        $this->compile();

        $clientEnabled ?
            $this->assertContainerBuilderNotHasAlias('netgen_layouts.http_cache.client') :
            $this->assertContainerBuilderHasAlias(
                'netgen_layouts.http_cache.client',
                'netgen_layouts.ezplatform.http_cache.client.local'
            );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsEzPlatformBundle\DependencyInjection\CompilerPass\HttpCache\ConfigureHttpCachePass::process
     */
    public function testProcessWithNoSupportedClient(): void
    {
        $this->setDefinition('netgen_layouts.http_cache.client', new Definition());
        $this->setDefinition('ezplatform.http_cache.purge_client_internal', new Definition(stdClass::class));

        $this->compile();

        $this->assertContainerBuilderNotHasAlias('netgen_layouts.http_cache.client');
    }

    public function processProvider(): array
    {
        return [
            [VarnishPurgeClient::class, true],
            [LocalPurgeClient::class, false],
        ];
    }

    /**
     * @covers \Netgen\Bundle\LayoutsEzPlatformBundle\DependencyInjection\CompilerPass\HttpCache\ConfigureHttpCachePass::process
     */
    public function testProcessWithEmptyContainer(): void
    {
        $this->compile();

        self::assertInstanceOf(FrozenParameterBag::class, $this->container->getParameterBag());
    }

    /**
     * Register the compiler pass under test.
     */
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ConfigureHttpCachePass());
    }

    /**
     * Assert that the ContainerBuilder for this test does not have an alias with the given id.
     */
    private function assertContainerBuilderNotHasAlias(string $aliasId): void
    {
        self::assertThat(
            $this->container,
            self::logicalNot(
                new ContainerBuilderHasAliasConstraint($aliasId)
            )
        );
    }
}
