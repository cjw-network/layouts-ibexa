<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ez\Tests\Layout\Resolver\TargetType;

use Netgen\Layouts\Ez\Layout\Resolver\TargetType\SemanticPathInfoPrefix;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;

final class SemanticPathInfoPrefixTest extends TestCase
{
    /**
     * @var \Netgen\Layouts\Ez\Layout\Resolver\TargetType\SemanticPathInfoPrefix
     */
    private $targetType;

    protected function setUp(): void
    {
        $this->targetType = new SemanticPathInfoPrefix();
    }

    /**
     * @covers \Netgen\Layouts\Ez\Layout\Resolver\TargetType\SemanticPathInfoPrefix::getType
     */
    public function testGetType(): void
    {
        self::assertSame('ez_semantic_path_info_prefix', $this->targetType::getType());
    }

    /**
     * @param mixed $value
     * @param bool $isValid
     *
     * @covers \Netgen\Layouts\Ez\Layout\Resolver\TargetType\SemanticPathInfoPrefix::getConstraints
     * @dataProvider validationProvider
     */
    public function testValidation($value, bool $isValid): void
    {
        $validator = Validation::createValidator();

        $errors = $validator->validate($value, $this->targetType->getConstraints());
        self::assertSame($isValid, $errors->count() === 0);
    }

    /**
     * @covers \Netgen\Layouts\Ez\Layout\Resolver\TargetType\SemanticPathInfoPrefix::provideValue
     */
    public function testProvideValue(): void
    {
        $request = Request::create('/the/answer');
        $request->attributes->set('semanticPathinfo', '/the/answer');

        self::assertSame(
            '/the/answer',
            $this->targetType->provideValue($request)
        );
    }

    /**
     * @covers \Netgen\Layouts\Ez\Layout\Resolver\TargetType\SemanticPathInfoPrefix::provideValue
     */
    public function testProvideValueWithEmptySemanticPathInfo(): void
    {
        $request = Request::create('/the/answer');
        $request->attributes->set('semanticPathinfo', false);

        self::assertSame(
            '/',
            $this->targetType->provideValue($request)
        );
    }

    /**
     * @covers \Netgen\Layouts\Ez\Layout\Resolver\TargetType\SemanticPathInfoPrefix::provideValue
     */
    public function testProvideValueWithNoSemanticPathInfo(): void
    {
        $request = Request::create('/the/answer');

        self::assertNull($this->targetType->provideValue($request));
    }

    /**
     * Provider for testing target type validation.
     */
    public function validationProvider(): array
    {
        return [
            ['/some/route', true],
            ['/', true],
            ['', false],
            [null, false],
        ];
    }
}
