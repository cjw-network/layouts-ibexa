<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Ez\Parameters\Form\Mapper;

use Netgen\BlockManager\Parameters\Form\Mapper;
use Netgen\BlockManager\Parameters\ParameterDefinition;
use Netgen\ContentBrowser\Form\Type\ContentBrowserType;

final class LocationMapper extends Mapper
{
    public function getFormType()
    {
        return ContentBrowserType::class;
    }

    public function mapOptions(ParameterDefinition $parameterDefinition)
    {
        return [
            'item_type' => 'ezlocation',
        ];
    }
}
