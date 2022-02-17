<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Layout\Resolver\Form\TargetType\Mapper;

use Netgen\ContentBrowser\Form\Type\ContentBrowserType;
use Netgen\Layouts\Layout\Resolver\Form\TargetType\Mapper;

final class Content extends Mapper
{
    public function getFormType(): string
    {
        return ContentBrowserType::class;
    }

    public function getFormOptions(): array
    {
        return [
            'item_type' => 'ibexa_content',
        ];
    }
}
