<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ez\Tests\Parameters\ParameterType;

use eZ\Publish\API\Repository\ObjectStateService;
use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\Core\Repository\Values\ObjectState\ObjectState as EzObjectState;
use eZ\Publish\Core\Repository\Values\ObjectState\ObjectStateGroup;
use Netgen\Layouts\Ez\Parameters\ParameterType\ObjectStateType;
use Netgen\Layouts\Ez\Tests\Validator\RepositoryValidatorFactory;
use Netgen\BlockManager\Parameters\ParameterDefinition;
use Netgen\BlockManager\Tests\Parameters\ParameterType\ParameterTypeTestTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Validation;

final class ObjectStateTypeTest extends TestCase
{
    use ParameterTypeTestTrait;

    /**
     * @var \eZ\Publish\API\Repository\Repository&\PHPUnit\Framework\MockObject\MockObject
     */
    private $repositoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $objectStateServiceMock;

    public function setUp(): void
    {
        $this->objectStateServiceMock = $this->createMock(ObjectStateService::class);
        $this->repositoryMock = $this->createPartialMock(Repository::class, ['sudo', 'getObjectStateService']);

        $this->repositoryMock
            ->expects(self::any())
            ->method('sudo')
            ->with(self::anything())
            ->willReturnCallback(
                function (callable $callback) {
                    return $callback($this->repositoryMock);
                }
            );

        $this->repositoryMock
            ->expects(self::any())
            ->method('getObjectStateService')
            ->willReturn($this->objectStateServiceMock);

        $this->type = new ObjectStateType();
    }

    /**
     * @covers \Netgen\Layouts\Ez\Parameters\ParameterType\ObjectStateType::getIdentifier
     */
    public function testGetIdentifier(): void
    {
        self::assertSame('ez_object_state', $this->type::getIdentifier());
    }

    /**
     * @covers \Netgen\Layouts\Ez\Parameters\ParameterType\ObjectStateType::configureOptions
     * @dataProvider validOptionsProvider
     */
    public function testValidOptions(array $options, array $resolvedOptions): void
    {
        $parameter = $this->getParameterDefinition($options);
        self::assertSame($resolvedOptions, $parameter->getOptions());
    }

    /**
     * @covers \Netgen\Layouts\Ez\Parameters\ParameterType\ObjectStateType::configureOptions
     * @dataProvider invalidOptionsProvider
     */
    public function testInvalidOptions(array $options): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->getParameterDefinition($options);
    }

    /**
     * Provider for testing valid parameter attributes.
     */
    public function validOptionsProvider(): array
    {
        return [
            [
                [],
                [
                    'multiple' => false,
                    'states' => [],
                ],
            ],
            [
                [
                    'multiple' => false,
                ],
                [
                    'multiple' => false,
                    'states' => [],
                ],
            ],
            [
                [
                    'multiple' => true,
                ],
                [
                    'multiple' => true,
                    'states' => [],
                ],
            ],
            [
                [
                    'states' => [],
                ],
                [
                    'multiple' => false,
                    'states' => [],
                ],
            ],
            [
                [
                    'states' => [42],
                ],
                [
                    'multiple' => false,
                    'states' => [42],
                ],
            ],
        ];
    }

    /**
     * Provider for testing invalid parameter attributes.
     */
    public function invalidOptionsProvider(): array
    {
        return [
            [
                [
                    'multiple' => 'true',
                ],
                [
                    'undefined_value' => 'Value',
                ],
            ],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $required
     * @param bool $isValid
     *
     * @covers \Netgen\Layouts\Ez\Parameters\ParameterType\ObjectStateType::getValueConstraints
     * @dataProvider validationProvider
     */
    public function testValidation($value, bool $required, bool $isValid): void
    {
        $group1 = new ObjectStateGroup(['identifier' => 'group1']);
        $group2 = new ObjectStateGroup(['identifier' => 'group2']);

        $this->objectStateServiceMock
            ->expects(self::at(0))
            ->method('loadObjectStateGroups')
            ->willReturn([$group1, $group2]);

        $this->objectStateServiceMock
            ->expects(self::at(1))
            ->method('loadObjectStates')
            ->with(self::identicalTo($group1))
            ->willReturn(
                [
                    new EzObjectState(
                        [
                            'identifier' => 'state1',
                        ]
                    ),
                    new EzObjectState(
                        [
                            'identifier' => 'state2',
                        ]
                    ),
                ]
            );

        $this->objectStateServiceMock
            ->expects(self::at(2))
            ->method('loadObjectStates')
            ->with(self::identicalTo($group2))
            ->willReturn([]);

        $options = $value !== null ? ['multiple' => is_array($value)] : [];
        $parameter = $this->getParameterDefinition($options, $required);
        $validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(new RepositoryValidatorFactory($this->repositoryMock))
            ->getValidator();

        $errors = $validator->validate($value, $this->type->getConstraints($parameter, $value));
        self::assertSame($isValid, $errors->count() === 0);
    }

    /**
     * @param mixed $value
     * @param bool $required
     * @param bool $isValid
     *
     * @covers \Netgen\Layouts\Ez\Parameters\ParameterType\ObjectStateType::getValueConstraints
     * @dataProvider validationWithEmptyValuesProvider
     */
    public function testValidationWithEmptyValues($value, bool $required, bool $isValid): void
    {
        $this->objectStateServiceMock
            ->expects(self::never())
            ->method('loadObjectStateGroups');

        $this->objectStateServiceMock
            ->expects(self::never())
            ->method('loadObjectStates');

        $options = $value !== null ? ['multiple' => is_array($value)] : [];
        $parameter = $this->getParameterDefinition($options, $required);
        $validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(new RepositoryValidatorFactory($this->repositoryMock))
            ->getValidator();

        $errors = $validator->validate($value, $this->type->getConstraints($parameter, $value));
        self::assertSame($isValid, $errors->count() === 0);
    }

    public function validationProvider(): array
    {
        return [
            ['group1|state2', false, true],
            [['group1|state2'], false, true],
            [['group1|state1', 'group1|state2'], false, true],
            [['group1|state1', 'group2|state1'], false, false],
            [['group2|state1'], false, false],
            [['unknown|state1'], false, false],
            [['group1|unknown'], false, false],
            ['group1|state2', true, true],
            [['group1|state2'], true, true],
            [['group1|state1', 'group1|state2'], true, true],
            [['group1|state1', 'group2|state1'], true, false],
            [['group2|state1'], true, false],
            [['unknown|state1'], true, false],
            [['group1|unknown'], true, false],
        ];
    }

    public function validationWithEmptyValuesProvider(): array
    {
        return [
            [[], false, true],
            [null, false, true],
            [[], true, false],
            [null, true, false],
        ];
    }

    /**
     * @param mixed $value
     * @param mixed $convertedValue
     * @param bool $multiple
     *
     * @covers \Netgen\Layouts\Ez\Parameters\ParameterType\ObjectStateType::fromHash
     * @dataProvider fromHashProvider
     */
    public function testFromHash($value, $convertedValue, bool $multiple): void
    {
        self::assertSame(
            $convertedValue,
            $this->type->fromHash(
                $this->getParameterDefinition(
                    [
                        'multiple' => $multiple,
                    ]
                ),
                $value
            )
        );
    }

    public function fromHashProvider(): array
    {
        return [
            [
                null,
                null,
                false,
            ],
            [
                [],
                null,
                false,
            ],
            [
                42,
                42,
                false,
            ],
            [
                [42, 43],
                42,
                false,
            ],
            [
                null,
                null,
                true,
            ],
            [
                [],
                null,
                true,
            ],
            [
                42,
                [42],
                true,
            ],
            [
                [42, 43],
                [42, 43],
                true,
            ],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $isEmpty
     *
     * @covers \Netgen\Layouts\Ez\Parameters\ParameterType\ObjectStateType::isValueEmpty
     * @dataProvider emptyProvider
     */
    public function testIsValueEmpty($value, bool $isEmpty): void
    {
        self::assertSame($isEmpty, $this->type->isValueEmpty(new ParameterDefinition(), $value));
    }

    /**
     * Provider for testing if the value is empty.
     */
    public function emptyProvider(): array
    {
        return [
            [null, true],
            [[], true],
            [42, false],
            [[42], false],
            [0, false],
            ['42', false],
            ['', false],
        ];
    }
}
