<?php

namespace Netgen\Bundle\EzPublishBlockManagerBundle\Tests\DependencyInjection\CompilerPass\View;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Netgen\Bundle\EzPublishBlockManagerBundle\DependencyInjection\CompilerPass\View\DefaultViewTemplatesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DefaultViewTemplatesPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @covers \Netgen\Bundle\EzPublishBlockManagerBundle\DependencyInjection\CompilerPass\View\DefaultViewTemplatesPass::process
     */
    public function testProcess()
    {
        $this->container->setParameter('ezpublish.siteaccess.list', array('cro', 'eng'));

        $this->container->setParameter(
            'netgen_block_manager.default.view',
            array(
                'test_view' => array(
                    'api' => array(
                        'override_match' => array(
                            'template' => 'override_api.html.twig',
                        ),
                    ),
                ),
            )
        );

        $this->container->setParameter(
            'netgen_block_manager.cro.view',
            array(
                'test_view' => array(
                    'default' => array(
                        'override_match' => array(
                            'template' => 'override_default.html.twig',
                        ),
                    ),
                ),
            )
        );

        $this->container->setParameter(
            'netgen_block_manager.default_view_templates',
            array(
                'test_view' => array(
                    'default' => 'default.html.twig',
                    'api' => 'api.html.twig',
                ),
                'other_view' => array(
                    'default' => 'default2.html.twig',
                    'api' => 'api2.html.twig',
                ),
            )
        );

        $this->compile();

        $this->assertContainerBuilderHasParameter(
            'netgen_block_manager.default.view',
            array(
                'test_view' => array(
                    'default' => array(
                        '___test_view_default_default___' => array(
                            'template' => 'default.html.twig',
                            'match' => array(),
                            'parameters' => array(),
                        ),
                    ),
                    'api' => array(
                        'override_match' => array(
                            'template' => 'override_api.html.twig',
                        ),
                        '___test_view_api_default___' => array(
                            'template' => 'api.html.twig',
                            'match' => array(),
                            'parameters' => array(),
                        ),
                    ),
                ),
                'other_view' => array(
                    'default' => array(
                        '___other_view_default_default___' => array(
                            'template' => 'default2.html.twig',
                            'match' => array(),
                            'parameters' => array(),
                        ),
                    ),
                    'api' => array(
                        '___other_view_api_default___' => array(
                            'template' => 'api2.html.twig',
                            'match' => array(),
                            'parameters' => array(),
                        ),
                    ),
                ),
            )
        );

        $this->assertContainerBuilderHasParameter(
            'netgen_block_manager.cro.view',
            array(
                'test_view' => array(
                    'default' => array(
                        'override_match' => array(
                            'template' => 'override_default.html.twig',
                        ),
                        '___test_view_default_default___' => array(
                            'template' => 'default.html.twig',
                            'match' => array(),
                            'parameters' => array(),
                        ),
                    ),
                    'api' => array(
                        '___test_view_api_default___' => array(
                            'template' => 'api.html.twig',
                            'match' => array(),
                            'parameters' => array(),
                        ),
                    ),
                ),
                'other_view' => array(
                    'default' => array(
                        '___other_view_default_default___' => array(
                            'template' => 'default2.html.twig',
                            'match' => array(),
                            'parameters' => array(),
                        ),
                    ),
                    'api' => array(
                        '___other_view_api_default___' => array(
                            'template' => 'api2.html.twig',
                            'match' => array(),
                            'parameters' => array(),
                        ),
                    ),
                ),
            )
        );

        $this->assertFalse($this->container->hasParameter('netgen_block_manager.eng.view'));
    }

    /**
     * @covers \Netgen\Bundle\EzPublishBlockManagerBundle\DependencyInjection\CompilerPass\View\DefaultViewTemplatesPass::process
     */
    public function testProcessWithEmptyContainer()
    {
        $this->compile();

        $this->assertEmpty($this->container->getAliases());
        // The container has at least self ("service_container") as the service
        $this->assertCount(1, $this->container->getServiceIds());
        $this->assertEmpty($this->container->getParameterBag()->all());
    }

    /**
     * Register the compiler pass under test.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new DefaultViewTemplatesPass());
    }
}
