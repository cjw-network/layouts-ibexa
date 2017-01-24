<?php

namespace Netgen\BlockManager\Ez\Tests\Layout\Resolver\TargetType;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Netgen\BlockManager\Ez\ContentProvider\ContentProviderInterface;
use Netgen\BlockManager\Ez\Layout\Resolver\TargetType\Children;
use Netgen\BlockManager\Ez\Tests\Validator\RepositoryValidatorFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class ChildrenTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentProviderMock;

    /**
     * @var \Netgen\BlockManager\Ez\Layout\Resolver\TargetType\Children
     */
    protected $targetType;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $locationServiceMock;

    public function setUp()
    {
        $this->contentProviderMock = $this->createMock(ContentProviderInterface::class);
        $this->locationServiceMock = $this->createMock(LocationService::class);
        $this->repositoryMock = $this->createPartialMock(Repository::class, array('sudo', 'getLocationService'));

        $this->repositoryMock
            ->expects($this->any())
            ->method('sudo')
            ->with($this->anything())
            ->will($this->returnCallback(function ($callback) {
                return $callback($this->repositoryMock);
            }));

        $this->repositoryMock
            ->expects($this->any())
            ->method('getLocationService')
            ->will($this->returnValue($this->locationServiceMock));

        $this->targetType = new Children($this->contentProviderMock);
    }

    /**
     * @covers \Netgen\BlockManager\Ez\Layout\Resolver\TargetType\Children::getType
     */
    public function testGetType()
    {
        $this->assertEquals('ezchildren', $this->targetType->getType());
    }

    /**
     * @param mixed $value
     * @param bool $isValid
     *
     * @covers \Netgen\BlockManager\Ez\Layout\Resolver\TargetType\Children::getConstraints
     * @dataProvider validationProvider
     */
    public function testValidation($value, $isValid)
    {
        if ($value !== null) {
            $this->locationServiceMock
                ->expects($this->once())
                ->method('loadLocation')
                ->with($this->equalTo($value))
                ->will(
                    $this->returnCallback(
                        function () use ($value) {
                            if (!is_int($value) || $value > 20) {
                                throw new NotFoundException('location', $value);
                            }
                        }
                    )
                );
        }

        $validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(new RepositoryValidatorFactory($this->repositoryMock))
            ->getValidator();

        $errors = $validator->validate($value, $this->targetType->getConstraints());
        $this->assertEquals($isValid, $errors->count() === 0);
    }

    /**
     * @covers \Netgen\BlockManager\Ez\Layout\Resolver\TargetType\Children::__construct
     * @covers \Netgen\BlockManager\Ez\Layout\Resolver\TargetType\Children::provideValue
     */
    public function testProvideValue()
    {
        $location = new Location(
            array(
                'parentLocationId' => 84,
            )
        );

        $this->contentProviderMock
            ->expects($this->any())
            ->method('provideLocation')
            ->will($this->returnValue($location));

        $this->assertEquals(84, $this->targetType->provideValue());
    }

    /**
     * @covers \Netgen\BlockManager\Ez\Layout\Resolver\TargetType\Children::__construct
     * @covers \Netgen\BlockManager\Ez\Layout\Resolver\TargetType\Children::provideValue
     */
    public function testProvideValueWithNoLocation()
    {
        $this->contentProviderMock
            ->expects($this->any())
            ->method('provideLocation')
            ->will($this->returnValue(null));

        $this->assertEquals(null, $this->targetType->provideValue());
    }

    /**
     * Provider for testing valid parameter values.
     *
     * @return array
     */
    public function validationProvider()
    {
        return array(
            array(12, true),
            array(24, false),
            array(-12, false),
            array(0, false),
            array('12', false),
            array('', false),
            array(null, false),
        );
    }
}