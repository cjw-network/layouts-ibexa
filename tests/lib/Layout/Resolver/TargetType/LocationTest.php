<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ez\Tests\Layout\Resolver\TargetType;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\Core\Repository\Values\Content\Location as EzLocation;
use Netgen\Layouts\Ez\ContentProvider\ContentExtractorInterface;
use Netgen\Layouts\Ez\Layout\Resolver\TargetType\Location;
use Netgen\Layouts\Ez\Tests\Validator\RepositoryValidatorFactory;
use Netgen\Layouts\Ez\Utils\RemoteIdConverter;
use Netgen\Layouts\Layout\Resolver\ValueObjectProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;

final class LocationTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\eZ\Publish\API\Repository\Repository
     */
    private MockObject $repositoryMock;

    private MockObject $locationServiceMock;

    private MockObject $contentExtractorMock;

    private MockObject $valueObjectProviderMock;

    private Location $targetType;

    protected function setUp(): void
    {
        $this->contentExtractorMock = $this->createMock(ContentExtractorInterface::class);
        $this->valueObjectProviderMock = $this->createMock(ValueObjectProviderInterface::class);
        $this->locationServiceMock = $this->createMock(LocationService::class);
        $this->repositoryMock = $this->createPartialMock(Repository::class, ['sudo', 'getLocationService']);

        $this->repositoryMock
            ->method('sudo')
            ->with(self::anything())
            ->willReturnCallback(
                fn (callable $callback) => $callback($this->repositoryMock),
            );

        $this->repositoryMock
            ->method('getLocationService')
            ->willReturn($this->locationServiceMock);

        $this->targetType = new Location(
            $this->contentExtractorMock,
            $this->valueObjectProviderMock,
            new RemoteIdConverter($this->repositoryMock),
        );
    }

    /**
     * @covers \Netgen\Layouts\Ez\Layout\Resolver\TargetType\Location::getType
     */
    public function testGetType(): void
    {
        self::assertSame('ez_location', $this->targetType::getType());
    }

    /**
     * @covers \Netgen\Layouts\Ez\Layout\Resolver\TargetType\Location::getConstraints
     */
    public function testValidation(): void
    {
        $this->locationServiceMock
            ->expects(self::once())
            ->method('loadLocation')
            ->with(self::identicalTo(42))
            ->willReturn(new EzLocation());

        $validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(new RepositoryValidatorFactory($this->repositoryMock))
            ->getValidator();

        $errors = $validator->validate(42, $this->targetType->getConstraints());
        self::assertCount(0, $errors);
    }

    /**
     * @covers \Netgen\Layouts\Ez\Layout\Resolver\TargetType\Location::getConstraints
     */
    public function testValidationWithInvalidValue(): void
    {
        $this->locationServiceMock
            ->expects(self::once())
            ->method('loadLocation')
            ->with(self::identicalTo(42))
            ->willThrowException(new NotFoundException('location', 42));

        $validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(new RepositoryValidatorFactory($this->repositoryMock))
            ->getValidator();

        $errors = $validator->validate(42, $this->targetType->getConstraints());
        self::assertCount(0, $errors);
    }

    /**
     * @covers \Netgen\Layouts\Ez\Layout\Resolver\TargetType\Location::__construct
     * @covers \Netgen\Layouts\Ez\Layout\Resolver\TargetType\Location::provideValue
     */
    public function testProvideValue(): void
    {
        $location = new EzLocation(
            [
                'id' => 42,
            ],
        );

        $request = Request::create('/');

        $this->contentExtractorMock
            ->method('extractLocation')
            ->with(self::identicalTo($request))
            ->willReturn($location);

        self::assertSame(42, $this->targetType->provideValue($request));
    }

    /**
     * @covers \Netgen\Layouts\Ez\Layout\Resolver\TargetType\Location::__construct
     * @covers \Netgen\Layouts\Ez\Layout\Resolver\TargetType\Location::provideValue
     */
    public function testProvideValueWithNoLocation(): void
    {
        $request = Request::create('/');

        $this->contentExtractorMock
            ->method('extractLocation')
            ->with(self::identicalTo($request))
            ->willReturn(null);

        self::assertNull($this->targetType->provideValue($request));
    }

    /**
     * @covers \Netgen\Layouts\Ez\Layout\Resolver\TargetType\Location::getValueObject
     */
    public function testGetValueObject(): void
    {
        $location = new EzLocation();

        $this->valueObjectProviderMock
            ->expects(self::once())
            ->method('getValueObject')
            ->with(self::identicalTo(42))
            ->willReturn($location);

        self::assertSame($location, $this->targetType->getValueObject(42));
    }

    /**
     * @covers \Netgen\Layouts\Ez\Layout\Resolver\TargetType\Location::export
     */
    public function testExport(): void
    {
        $this->locationServiceMock
            ->expects(self::once())
            ->method('loadLocation')
            ->with(self::identicalTo(42))
            ->willReturn(new EzLocation(['remoteId' => 'abc']));

        self::assertSame('abc', $this->targetType->export(42));
    }

    /**
     * @covers \Netgen\Layouts\Ez\Layout\Resolver\TargetType\Location::export
     */
    public function testExportWithInvalidValue(): void
    {
        $this->locationServiceMock
            ->expects(self::once())
            ->method('loadLocation')
            ->with(self::identicalTo(42))
            ->willThrowException(new NotFoundException('location', 42));

        self::assertNull($this->targetType->export(42));
    }

    /**
     * @covers \Netgen\Layouts\Ez\Layout\Resolver\TargetType\Location::import
     */
    public function testImport(): void
    {
        $this->locationServiceMock
            ->expects(self::once())
            ->method('loadLocationByRemoteId')
            ->with(self::identicalTo('abc'))
            ->willReturn(new EzLocation(['id' => 42]));

        self::assertSame(42, $this->targetType->import('abc'));
    }

    /**
     * @covers \Netgen\Layouts\Ez\Layout\Resolver\TargetType\Location::import
     */
    public function testImportWithInvalidValue(): void
    {
        $this->locationServiceMock
            ->expects(self::once())
            ->method('loadLocationByRemoteId')
            ->with(self::identicalTo('abc'))
            ->willThrowException(new NotFoundException('location', 'abc'));

        self::assertSame(0, $this->targetType->import('abc'));
    }
}
