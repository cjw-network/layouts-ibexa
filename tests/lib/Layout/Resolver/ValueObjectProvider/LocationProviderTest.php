<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ez\Tests\Layout\Resolver\ValueObjectProvider;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Netgen\Layouts\Ez\Layout\Resolver\ValueObjectProvider\LocationProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class LocationProviderTest extends TestCase
{
    private MockObject $repositoryMock;

    private MockObject $locationServiceMock;

    private LocationProvider $valueObjectProvider;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(Repository::class);
        $this->locationServiceMock = $this->createMock(LocationService::class);

        $this->repositoryMock
            ->expects(self::any())
            ->method('getLocationService')
            ->willReturn($this->locationServiceMock);

        $this->repositoryMock
            ->expects(self::any())
            ->method('sudo')
            ->with(self::anything())
            ->willReturnCallback(
                fn (callable $callback) => $callback($this->repositoryMock),
            );

        $this->valueObjectProvider = new LocationProvider(
            $this->repositoryMock,
        );
    }

    /**
     * @covers \Netgen\Layouts\Ez\Layout\Resolver\ValueObjectProvider\LocationProvider::__construct
     * @covers \Netgen\Layouts\Ez\Layout\Resolver\ValueObjectProvider\LocationProvider::getValueObject
     */
    public function testGetValueObject(): void
    {
        $location = new Location();

        $this->locationServiceMock
            ->expects(self::any())
            ->method('loadLocation')
            ->with(self::identicalTo(42))
            ->willReturn($location);

        self::assertSame($location, $this->valueObjectProvider->getValueObject(42));
    }

    /**
     * @covers \Netgen\Layouts\Ez\Layout\Resolver\ValueObjectProvider\LocationProvider::getValueObject
     */
    public function testGetValueObjectWithNonExistentLocation(): void
    {
        $this->locationServiceMock
            ->expects(self::any())
            ->method('loadLocation')
            ->with(self::identicalTo(42))
            ->willThrowException(new NotFoundException('location', 42));

        self::assertNull($this->valueObjectProvider->getValueObject(42));
    }
}
