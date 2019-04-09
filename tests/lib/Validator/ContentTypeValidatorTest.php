<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ez\Tests\Validator;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType as EzContentType;
use eZ\Publish\Core\Repository\Values\ContentType\ContentTypeGroup;
use Netgen\Layouts\Ez\Validator\Constraint\ContentType;
use Netgen\Layouts\Ez\Validator\ContentTypeValidator;
use Netgen\BlockManager\Tests\TestCase\ValidatorTestCase;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class ContentTypeValidatorTest extends ValidatorTestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $repositoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $contentTypeServiceMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->constraint = new ContentType();
    }

    /**
     * @covers \Netgen\Layouts\Ez\Validator\ContentTypeValidator::__construct
     * @covers \Netgen\Layouts\Ez\Validator\ContentTypeValidator::validate
     * @dataProvider  validateDataProvider
     */
    public function testValidate(string $identifier, array $groups, array $allowedTypes, bool $isValid): void
    {
        $this->contentTypeServiceMock
            ->expects(self::once())
            ->method('loadContentTypeByIdentifier')
            ->with(self::identicalTo($identifier))
            ->willReturn(
                new EzContentType(
                    [
                        'identifier' => $identifier,
                        'contentTypeGroups' => array_map(
                            static function (string $group): ContentTypeGroup {
                                return new ContentTypeGroup(
                                    [
                                        'identifier' => $group,
                                    ]
                                );
                            },
                            $groups
                        ),
                    ]
                )
            );

        $this->constraint->allowedTypes = $allowedTypes;
        self::assertValid($isValid, $identifier);
    }

    /**
     * @covers \Netgen\Layouts\Ez\Validator\ContentTypeValidator::__construct
     * @covers \Netgen\Layouts\Ez\Validator\ContentTypeValidator::validate
     */
    public function testValidateNull(): void
    {
        $this->contentTypeServiceMock
            ->expects(self::never())
            ->method('loadContentTypeByIdentifier');

        self::assertValid(true, null);
    }

    /**
     * @covers \Netgen\Layouts\Ez\Validator\ContentTypeValidator::__construct
     * @covers \Netgen\Layouts\Ez\Validator\ContentTypeValidator::validate
     */
    public function testValidateInvalid(): void
    {
        $this->contentTypeServiceMock
            ->expects(self::once())
            ->method('loadContentTypeByIdentifier')
            ->with(self::identicalTo('unknown'))
            ->willThrowException(new NotFoundException('content type', 'unknown'));

        self::assertValid(false, 'unknown');
    }

    /**
     * @covers \Netgen\Layouts\Ez\Validator\ContentTypeValidator::validate
     */
    public function testValidateThrowsUnexpectedTypeExceptionWithInvalidConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "Netgen\\Layouts\\Ez\\Validator\\Constraint\\ContentType", "Symfony\\Component\\Validator\\Constraints\\NotBlank" given');

        $this->constraint = new NotBlank();
        self::assertValid(true, 'value');
    }

    /**
     * @covers \Netgen\Layouts\Ez\Validator\ContentTypeValidator::validate
     */
    public function testValidateThrowsUnexpectedTypeExceptionWithInvalidValue(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "string", "integer" given');

        self::assertValid(true, 42);
    }

    /**
     * @covers \Netgen\Layouts\Ez\Validator\ContentTypeValidator::validate
     */
    public function testValidateThrowsUnexpectedTypeExceptionWithInvalidAllowedTypes(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "array", "integer" given');

        $this->constraint->allowedTypes = 42;
        self::assertValid(true, 'article');
    }

    public function validateDataProvider(): array
    {
        return [
            ['article', ['group1'], [], true],
            ['article', ['group1'], ['group2' => true], true],
            ['article', ['group1'], ['group1' => true], true],
            ['article', ['group1'], ['group1' => false], false],
            ['article', ['group1'], ['group1' => []], false],
            ['article', ['group1'], ['group1' => ['article']], true],
            ['article', ['group1'], ['group1' => ['news']], false],
            ['article', ['group1'], ['group1' => ['article', 'news']], true],
        ];
    }

    protected function getValidator(): ConstraintValidatorInterface
    {
        $this->contentTypeServiceMock = $this->createMock(ContentTypeService::class);
        $this->repositoryMock = $this->createPartialMock(Repository::class, ['sudo', 'getContentTypeService']);

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
            ->method('getContentTypeService')
            ->willReturn($this->contentTypeServiceMock);

        return new ContentTypeValidator($this->repositoryMock);
    }
}
