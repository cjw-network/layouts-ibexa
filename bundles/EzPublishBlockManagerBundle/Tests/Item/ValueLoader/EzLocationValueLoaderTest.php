<?php

namespace Netgen\Bundle\EzPublishBlockManagerBundle\Tests\Item\ValueLoader;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Netgen\Bundle\EzPublishBlockManagerBundle\Item\ValueLoader\EzLocationValueLoader;
use Exception;

class EzLocationValueLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $locationServiceMock;

    /**
     * @var \Netgen\Bundle\EzPublishBlockManagerBundle\Item\ValueLoader\EzLocationValueLoader
     */
    protected $valueLoader;

    public function setUp()
    {
        $this->locationServiceMock = $this->createMock(LocationService::class);

        $this->valueLoader = new EzLocationValueLoader($this->locationServiceMock);
    }

    /**
     * @covers \Netgen\Bundle\EzPublishBlockManagerBundle\Item\ValueLoader\EzLocationValueLoader::__construct
     * @covers \Netgen\Bundle\EzPublishBlockManagerBundle\Item\ValueLoader\EzLocationValueLoader::load
     */
    public function testLoad()
    {
        $this->locationServiceMock
            ->expects($this->any())
            ->method('loadLocation')
            ->with($this->isType('int'))
            ->will($this->returnCallback(
                function ($id) { return new Location(array('id' => $id)); })
            );

        $location = $this->valueLoader->load(52);

        self::assertEquals(new Location(array('id' => 52)), $location);
    }

    /**
     * @covers \Netgen\Bundle\EzPublishBlockManagerBundle\Item\ValueLoader\EzLocationValueLoader::load
     * @expectedException \Netgen\BlockManager\Exception\InvalidItemException
     */
    public function testLoadThrowsInvalidItemException()
    {
        $this->locationServiceMock
            ->expects($this->any())
            ->method('loadLocation')
            ->with($this->isType('int'))
            ->will($this->throwException(new Exception()));

        $this->valueLoader->load(52);
    }

    /**
     * @covers \Netgen\Bundle\EzPublishBlockManagerBundle\Item\ValueLoader\EzLocationValueLoader::getValueType
     */
    public function testGetValueType()
    {
        self::assertEquals('ezlocation', $this->valueLoader->getValueType());
    }
}
