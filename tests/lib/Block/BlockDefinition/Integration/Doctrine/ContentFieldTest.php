<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Block\BlockDefinition\Integration\Doctrine;

use Netgen\Layouts\Ibexa\Tests\Block\BlockDefinition\Integration\ContentFieldTestBase;
use Netgen\Layouts\Tests\Persistence\Doctrine\TestCaseTrait;

/**
 * @covers \Netgen\Layouts\Ibexa\Block\BlockDefinition\Handler\ContentFieldHandler::buildParameters
 */
final class ContentFieldTest extends ContentFieldTestBase
{
    use TestCaseTrait;

    protected function tearDown(): void
    {
        $this->closeDatabase();
    }
}
