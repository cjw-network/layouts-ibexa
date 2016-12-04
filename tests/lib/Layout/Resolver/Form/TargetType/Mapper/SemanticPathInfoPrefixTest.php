<?php

namespace Netgen\BlockManager\Ez\Tests\Layout\Resolver\Form\TargetType\Mapper;

use Netgen\BlockManager\Ez\Layout\Resolver\Form\TargetType\Mapper\SemanticPathInfoPrefix;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SemanticPathInfoPrefixTest extends TestCase
{
    /**
     * @var \Netgen\BlockManager\Layout\Resolver\Form\TargetType\MapperInterface
     */
    protected $mapper;

    public function setUp()
    {
        $this->mapper = new SemanticPathInfoPrefix();
    }

    /**
     * @covers \Netgen\BlockManager\Ez\Layout\Resolver\Form\TargetType\Mapper\SemanticPathInfoPrefix::getFormType
     */
    public function testGetFormType()
    {
        $this->assertEquals(TextType::class, $this->mapper->getFormType());
    }
}
